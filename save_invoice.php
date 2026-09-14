<?php
header('Content-Type: application/json');

$pdo = new PDO("mysql:host=localhost;dbname=afromed_db", 'root', '');
$data = json_decode(file_get_contents('php://input'), true);

if ($data) {
    try {
        $pdo->beginTransaction();

        $stmt = $pdo->prepare("
            INSERT INTO invoices (
                invoice_type, company_name, invoice_no, invoice_date, invoice_to, customer, 
                dos, case_number, medical_aid, medical_aid_no, id_number, auth_reference,
                event_name, event_venue, event_date, event_time,
                subtotal, vat, total, payments_credits, balance_due
            ) VALUES (
                :type, :comp, :no, :date, :to, :cust,
                :dos, :case, :aid, :aid_no, :id, :auth,
                :ev_name, :ev_ven, :ev_date, :ev_time,
                :sub, :vat, :tot, :pay, :bal
            )
        ");
        
        // Use ?? null to safely insert whichever fields are present for the selected layout type
        $stmt->execute([
            ':type' => $data['type'],
            ':comp' => $data['company'],
            ':no' => $data['invoice_no'],
            ':date' => $data['date'],
            ':to' => $data['invoice_to'],
            ':cust' => $data['customer'] ?? null,
            ':dos' => $data['dos'] ?? null,
            ':case' => $data['case_number'] ?? null,
            ':aid' => $data['medical_aid'] ?? null,
            ':aid_no' => $data['medical_aid_no'] ?? null,
            ':id' => $data['id_number'] ?? null,
            ':auth' => $data['auth_reference'] ?? null,
            ':ev_name' => $data['event_name'] ?? null,
            ':ev_ven' => $data['event_venue'] ?? null,
            ':ev_date' => $data['event_date'] ?? null,
            ':ev_time' => $data['event_time'] ?? null,
            ':sub' => $data['subtotal'] ?? 0,
            ':vat' => $data['vat'] ?? 0,
            ':tot' => $data['total'] ?? 0,
            ':pay' => $data['payments'] ?? 0,
            ':bal' => $data['balance'] ?? 0
        ]);

        $inv_id = $pdo->lastInsertId();
        $stmt_item = $pdo->prepare("INSERT INTO invoice_items (invoice_id, item_no, description, qty, rate, icd_10, vat_status, amount) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");

        foreach ($data['items'] as $item) {
            $stmt_item->execute([
                $inv_id, 
                $item['item_no'] ?? null, 
                $item['description'], 
                $item['qty'], 
                $item['rate'], 
                $item['icd_10'] ?? null, 
                $item['vat_status'] ?? null, 
                $item['amount']
            ]);
        }

        $pdo->commit();
        echo json_encode(['success' => true]);
    } catch(Exception $e) {
        $pdo->rollBack();
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}
?>