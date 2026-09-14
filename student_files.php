<?php
declare(strict_types=1);

/**
 * Afromed Emergency College - Student file service
 *
 * Endpoints:
 *   POST student_files.php                      Upload multipart file
 *   GET  student_files.php?action=list&studentId=...
 *   GET  student_files.php?action=list_all
 *   GET  student_files.php?action=file&studentId=...&fileId=...
 *   POST student_files.php                      JSON {"action":"delete","studentId":"...","fileId":"..."}
 *
 * Authentication:
 *   Authorization: Bearer <Firebase ID token>
 *
 * The endpoint validates the Firebase token, then checks the signed-in user's
 * role from /users/{uid} in Firestore via the REST API using the same ID token.
 */

const FIREBASE_API_KEY = 'AIzaSyBBkouWWkEWAlPtLSmp3eHZVVBcQtYu-xo';
const FIREBASE_PROJECT_ID = 'afromed-fda57';

header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: same-origin');

function json_response(array $payload, int $status = 200): void {
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($payload, JSON_UNESCAPED_SLASHES);
    exit;
}

function request_origin_allowed(): void {
    $origin = $_SERVER['HTTP_ORIGIN'] ?? '';
    if ($origin === '') {
        return; // same-origin requests commonly omit Origin
    }

    $allowed = [
        'https://afromed-admin.co.za',
        'https://www.afromed-admin.co.za',
        'http://localhost:8000',
        'http://127.0.0.1:8000',
    ];

    $extra = getenv('AFROMED_ALLOWED_ORIGIN');
    if ($extra) {
        foreach (explode(',', $extra) as $item) {
            $item = trim($item);
            if ($item !== '') $allowed[] = $item;
        }
    }

    if (!in_array($origin, $allowed, true)) {
        json_response(['success' => false, 'error' => 'Origin not allowed.'], 403);
    }

    header('Access-Control-Allow-Origin: ' . $origin);
    header('Vary: Origin');
    header('Access-Control-Allow-Credentials: true');
}

request_origin_allowed();

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
    header('Access-Control-Allow-Headers: Authorization, Content-Type');
    http_response_code(204);
    exit;
}

function bearer_token(): string {
    $header = $_SERVER['HTTP_AUTHORIZATION'] ?? '';

    if ($header === '' && function_exists('getallheaders')) {
        $headers = getallheaders();
        $header = $headers['Authorization'] ?? $headers['authorization'] ?? '';
    }

    if (!preg_match('/^Bearer\s+(.+)$/i', trim($header), $m)) {
        json_response(['success' => false, 'error' => 'Authentication token missing.'], 401);
    }

    return trim($m[1]);
}

function curl_json(string $url, string $method = 'GET', ?array $body = null, array $headers = []): array {
    if (!function_exists('curl_init')) {
        json_response(['success' => false, 'error' => 'PHP cURL extension is required on the server.'], 500);
    }

    $ch = curl_init($url);
    $baseHeaders = ['Accept: application/json'];

    if ($body !== null) {
        $baseHeaders[] = 'Content-Type: application/json';
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));
    }

    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST => $method,
        CURLOPT_HTTPHEADER => array_merge($baseHeaders, $headers),
        CURLOPT_CONNECTTIMEOUT => 10,
        CURLOPT_TIMEOUT => 20,
        CURLOPT_FOLLOWLOCATION => false,
    ]);

    $raw = curl_exec($ch);
    $http = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $err = curl_error($ch);
    curl_close($ch);

    if ($raw === false) {
        json_response(['success' => false, 'error' => 'Authentication service unavailable: ' . $err], 502);
    }

    $data = json_decode($raw, true);
    if (!is_array($data)) $data = [];

    return ['status' => $http, 'data' => $data];
}

function firestore_string(array $field): ?string {
    if (isset($field['stringValue'])) return (string)$field['stringValue'];
    return null;
}

function verify_admin(string $idToken): array {
    // First validate that the Firebase ID token is real/current.
    $lookupUrl = 'https://identitytoolkit.googleapis.com/v1/accounts:lookup?key=' . rawurlencode(FIREBASE_API_KEY);
    $lookup = curl_json($lookupUrl, 'POST', ['idToken' => $idToken]);

    if ($lookup['status'] !== 200 || empty($lookup['data']['users'][0]['localId'])) {
        json_response(['success' => false, 'error' => 'Invalid or expired login session.'], 401);
    }

    $firebaseUser = $lookup['data']['users'][0];
    $uid = (string)$firebaseUser['localId'];
    $email = (string)($firebaseUser['email'] ?? '');

    // Allow a custom claim when one is configured.
    $custom = [];
    if (!empty($firebaseUser['customAttributes'])) {
        $decoded = json_decode((string)$firebaseUser['customAttributes'], true);
        if (is_array($decoded)) $custom = $decoded;
    }

    $allowedRoles = ['admin', 'administrator', 'staff', 'college_admin', 'collegeadmin'];
    $customRole = strtolower((string)($custom['role'] ?? ''));
    if (($custom['admin'] ?? false) === true || in_array($customRole, $allowedRoles, true)) {
        return ['uid' => $uid, 'email' => $email, 'role' => $customRole ?: 'admin'];
    }

    // Otherwise verify role from Firestore /users/{uid} using the authenticated user's own token.
    $userUrl = 'https://firestore.googleapis.com/v1/projects/' . rawurlencode(FIREBASE_PROJECT_ID)
        . '/databases/(default)/documents/users/' . rawurlencode($uid);

    $userDoc = curl_json(
        $userUrl,
        'GET',
        null,
        ['Authorization: Bearer ' . $idToken]
    );

    $role = '';
    if ($userDoc['status'] === 200 && isset($userDoc['data']['fields']['role'])) {
        $role = strtolower((string)(firestore_string($userDoc['data']['fields']['role']) ?? ''));
    }

    if (!in_array($role, $allowedRoles, true)) {
        json_response([
            'success' => false,
            'error' => 'Your account is signed in but is not authorised for College file administration.'
        ], 403);
    }

    return ['uid' => $uid, 'email' => $email, 'role' => $role];
}

function safe_student_id(string $value): string {
    $value = trim($value);
    if ($value === '' || !preg_match('/^[A-Za-z0-9_-]{6,160}$/', $value)) {
        json_response(['success' => false, 'error' => 'Invalid student reference.'], 400);
    }
    return $value;
}

function safe_file_id(string $value): string {
    $value = trim($value);
    if ($value === '' || !preg_match('/^[A-Fa-f0-9]{24,64}$/', $value)) {
        json_response(['success' => false, 'error' => 'Invalid file reference.'], 400);
    }
    return $value;
}

function storage_root(): string {
    $custom = getenv('AFROMED_STUDENT_UPLOAD_DIR');
    $root = $custom ? rtrim($custom, DIRECTORY_SEPARATOR) : (__DIR__ . DIRECTORY_SEPARATOR . 'private_student_uploads');

    if (!is_dir($root) && !mkdir($root, 0750, true) && !is_dir($root)) {
        json_response(['success' => false, 'error' => 'Unable to initialise student file storage.'], 500);
    }

    // Apache protection for the fallback web-root directory.
    $htaccess = $root . DIRECTORY_SEPARATOR . '.htaccess';
    if (!file_exists($htaccess)) {
        @file_put_contents($htaccess, "Options -Indexes\n<IfModule mod_authz_core.c>\nRequire all denied\n</IfModule>\n<IfModule !mod_authz_core.c>\nDeny from all\n</IfModule>\n");
    }

    $index = $root . DIRECTORY_SEPARATOR . 'index.html';
    if (!file_exists($index)) {
        @file_put_contents($index, '');
    }

    return $root;
}

function meta_dir(): string {
    $dir = storage_root() . DIRECTORY_SEPARATOR . '_meta';
    if (!is_dir($dir) && !mkdir($dir, 0750, true) && !is_dir($dir)) {
        json_response(['success' => false, 'error' => 'Unable to initialise file metadata storage.'], 500);
    }
    return $dir;
}

function meta_file(string $studentId): string {
    return meta_dir() . DIRECTORY_SEPARATOR . $studentId . '.json';
}

function read_meta(string $studentId): array {
    $file = meta_file($studentId);
    if (!file_exists($file)) return [];

    $raw = @file_get_contents($file);
    if ($raw === false || trim($raw) === '') return [];

    $data = json_decode($raw, true);
    return is_array($data) ? array_values($data) : [];
}

function write_meta(string $studentId, array $files): void {
    $file = meta_file($studentId);
    $fp = @fopen($file, 'c+');
    if (!$fp) {
        json_response(['success' => false, 'error' => 'Unable to update file metadata.'], 500);
    }

    if (!flock($fp, LOCK_EX)) {
        fclose($fp);
        json_response(['success' => false, 'error' => 'Unable to lock file metadata.'], 500);
    }

    ftruncate($fp, 0);
    rewind($fp);
    fwrite($fp, json_encode(array_values($files), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    fflush($fp);
    flock($fp, LOCK_UN);
    fclose($fp);
}

function allowed_types(): array {
    return ['photo', 'identity', 'records', 'results', 'other'];
}

function mime_extension(string $mime): ?string {
    $map = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
        'application/pdf' => 'pdf',
        'application/msword' => 'doc',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'docx',
        'application/vnd.ms-excel' => 'xls',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => 'xlsx',
        'text/csv' => 'csv',
        'text/plain' => 'txt',
    ];
    return $map[$mime] ?? null;
}

function find_file_meta(string $studentId, string $fileId): array {
    foreach (read_meta($studentId) as $item) {
        if (($item['id'] ?? '') === $fileId) return $item;
    }
    json_response(['success' => false, 'error' => 'Student file not found.'], 404);
}

$idToken = bearer_token();
$admin = verify_admin($idToken);

$method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
$action = $_GET['action'] ?? '';

if ($method === 'GET' && $action === 'list') {
    $studentId = safe_student_id((string)($_GET['studentId'] ?? ''));
    json_response(['success' => true, 'files' => read_meta($studentId)]);
}

if ($method === 'GET' && $action === 'list_all') {
    $all = [];
    foreach (glob(meta_dir() . DIRECTORY_SEPARATOR . '*.json') ?: [] as $metaPath) {
        $studentId = pathinfo($metaPath, PATHINFO_FILENAME);
        if (!preg_match('/^[A-Za-z0-9_-]{6,160}$/', $studentId)) continue;
        foreach (read_meta($studentId) as $item) $all[] = $item;
    }
    json_response(['success' => true, 'files' => $all]);
}

if ($method === 'GET' && $action === 'file') {
    $studentId = safe_student_id((string)($_GET['studentId'] ?? ''));
    $fileId = safe_file_id((string)($_GET['fileId'] ?? ''));
    $meta = find_file_meta($studentId, $fileId);

    $relative = (string)($meta['relativePath'] ?? '');
    $absolute = storage_root() . DIRECTORY_SEPARATOR . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $relative);

    $realRoot = realpath(storage_root());
    $realFile = realpath($absolute);

    if (!$realRoot || !$realFile || strpos($realFile, $realRoot . DIRECTORY_SEPARATOR) !== 0 || !is_file($realFile)) {
        json_response(['success' => false, 'error' => 'Stored file is missing.'], 404);
    }

    $mime = (string)($meta['contentType'] ?? 'application/octet-stream');
    $name = (string)($meta['originalName'] ?? 'student-file');

    header('Content-Type: ' . $mime);
    header('Content-Length: ' . filesize($realFile));
    header('Content-Disposition: inline; filename="' . str_replace('"', '', basename($name)) . '"');
    header('Cache-Control: private, max-age=300');
    readfile($realFile);
    exit;
}

if ($method === 'POST') {
    $contentType = $_SERVER['CONTENT_TYPE'] ?? '';

    // JSON action (currently delete)
    if (stripos($contentType, 'application/json') !== false) {
        $payload = json_decode((string)file_get_contents('php://input'), true);
        if (!is_array($payload)) {
            json_response(['success' => false, 'error' => 'Invalid JSON request.'], 400);
        }

        if (($payload['action'] ?? '') === 'delete') {
            $studentId = safe_student_id((string)($payload['studentId'] ?? ''));
            $fileId = safe_file_id((string)($payload['fileId'] ?? ''));

            $items = read_meta($studentId);
            $found = null;
            $remaining = [];

            foreach ($items as $item) {
                if (($item['id'] ?? '') === $fileId) {
                    $found = $item;
                } else {
                    $remaining[] = $item;
                }
            }

            if (!$found) {
                json_response(['success' => false, 'error' => 'Student file not found.'], 404);
            }

            $relative = (string)($found['relativePath'] ?? '');
            $absolute = storage_root() . DIRECTORY_SEPARATOR . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $relative);

            $realRoot = realpath(storage_root());
            $realFile = realpath($absolute);

            if ($realRoot && $realFile && strpos($realFile, $realRoot . DIRECTORY_SEPARATOR) === 0 && is_file($realFile)) {
                @unlink($realFile);
            }

            write_meta($studentId, $remaining);
            json_response(['success' => true]);
        }

        json_response(['success' => false, 'error' => 'Unsupported action.'], 400);
    }

    // Multipart upload
    $studentId = safe_student_id((string)($_POST['studentId'] ?? ''));
    $studentNo = trim((string)($_POST['studentNo'] ?? ''));
    $studentName = trim((string)($_POST['studentName'] ?? ''));
    $type = strtolower(trim((string)($_POST['type'] ?? 'other')));
    $label = trim((string)($_POST['label'] ?? ''));

    if (!in_array($type, allowed_types(), true)) {
        json_response(['success' => false, 'error' => 'Unsupported student file category.'], 400);
    }

    if (!isset($_FILES['file']) || !is_array($_FILES['file'])) {
        json_response(['success' => false, 'error' => 'No file was uploaded.'], 400);
    }

    $upload = $_FILES['file'];

    if (($upload['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
        json_response(['success' => false, 'error' => 'Upload failed with code ' . (int)$upload['error'] . '.'], 400);
    }

    $tmp = (string)$upload['tmp_name'];
    $size = (int)$upload['size'];
    $originalName = basename((string)$upload['name']);

    $max = ($type === 'photo') ? (5 * 1024 * 1024) : (15 * 1024 * 1024);
    if ($size <= 0 || $size > $max) {
        json_response([
            'success' => false,
            'error' => 'File exceeds the allowed size for this category.'
        ], 413);
    }

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = (string)$finfo->file($tmp);
    $extension = mime_extension($mime);

    if ($extension === null) {
        json_response(['success' => false, 'error' => 'Unsupported file type.'], 415);
    }

    if ($type === 'photo' && strpos($mime, 'image/') !== 0) {
        json_response(['success' => false, 'error' => 'Student photo must be an image.'], 415);
    }

    $fileId = bin2hex(random_bytes(16));
    $storedName = $fileId . '.' . $extension;

    $relativeDir = $studentId . '/' . $type;
    $absoluteDir = storage_root() . DIRECTORY_SEPARATOR . $studentId . DIRECTORY_SEPARATOR . $type;

    if (!is_dir($absoluteDir) && !mkdir($absoluteDir, 0750, true) && !is_dir($absoluteDir)) {
        json_response(['success' => false, 'error' => 'Unable to create student storage folder.'], 500);
    }

    $destination = $absoluteDir . DIRECTORY_SEPARATOR . $storedName;
    if (!move_uploaded_file($tmp, $destination)) {
        json_response(['success' => false, 'error' => 'Server could not save the uploaded file.'], 500);
    }

    @chmod($destination, 0640);

    $meta = [
        'id' => $fileId,
        'studentId' => $studentId,
        'studentNo' => $studentNo,
        'studentName' => $studentName,
        'type' => $type,
        'label' => $label !== '' ? $label : ucfirst($type),
        'originalName' => $originalName,
        'storedName' => $storedName,
        'relativePath' => $relativeDir . '/' . $storedName,
        'contentType' => $mime,
        'size' => $size,
        'uploadedAt' => gmdate('c'),
        'uploadedBy' => $admin['uid'],
        'uploadedByEmail' => $admin['email'],
    ];

    $items = read_meta($studentId);

    // Keep only one current profile photo. Older photos are deleted when replaced.
    if ($type === 'photo') {
        $kept = [];
        foreach ($items as $item) {
            if (($item['type'] ?? '') === 'photo') {
                $oldRelative = (string)($item['relativePath'] ?? '');
                $oldAbsolute = storage_root() . DIRECTORY_SEPARATOR . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $oldRelative);
                $oldReal = realpath($oldAbsolute);
                $realRoot = realpath(storage_root());
                if ($oldReal && $realRoot && strpos($oldReal, $realRoot . DIRECTORY_SEPARATOR) === 0 && is_file($oldReal)) {
                    @unlink($oldReal);
                }
            } else {
                $kept[] = $item;
            }
        }
        $items = $kept;
    }

    $items[] = $meta;
    write_meta($studentId, $items);

    json_response(['success' => true, 'file' => $meta], 201);
}

json_response(['success' => false, 'error' => 'Unsupported request.'], 405);
