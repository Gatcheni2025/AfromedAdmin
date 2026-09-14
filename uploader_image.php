<?php
// uploader_image.php

// 1. Allow Cross-Origin Requests (CORS) so the app can communicate with this script
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Content-Type: application/json");

// 2. Define the directory where images will be saved
$target_dir = "drivers_images/";

// Create the directory if it doesn't exist
if (!file_exists($target_dir)) {
    mkdir($target_dir, 0755, true);
}

// 3. Process the incoming upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file'])) {
    
    // Get the User UID to name the file (defaults to 'unknown' if not provided)
    $uid = isset($_POST['uid']) ? preg_replace('/[^a-zA-Z0-9_-]/', '', $_POST['uid']) : 'unknown';
    $file = $_FILES['file'];

    // Verify it is actually an image
    $check = getimagesize($file["tmp_name"]);
    if($check === false) {
        echo json_encode(['success' => false, 'error' => 'File is not a valid image.']);
        exit;
    }

    // Force extension to .jpg since the app will compress it to JPEG
    $filename = $uid . '_' . time() . '.jpg';
    $target_file = $target_dir . $filename;

    // Move the uploaded file to the drivers_images directory
    if (move_uploaded_file($file["tmp_name"], $target_file)) {
        
        // Construct the full public URL
        // Make sure you use https://
        $url = "https://afromed-admin.co.za/" . $target_file;
        
        // Return success and the new image URL
        echo json_encode(['success' => true, 'url' => $url]);
        
    } else {
        echo json_encode(['success' => false, 'error' => 'Server failed to save the file. Check folder permissions.']);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'No file uploaded or invalid request.']);
}
?>