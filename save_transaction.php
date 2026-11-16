<?php
require_once 'db.php';
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");

// Get the JSON input sent from index.html fetch()
$data = json_decode(file_get_contents("php://input"), true);

if ($data && !empty($data['description']) && isset($data['amount'])) {
    try {
        // Ensure your 'transactions' table has columns: id, user_email, description, amount, type, category, date
        $stmt = $db->prepare("INSERT INTO transactions (user_email, description, amount, type, category, date) 
                              VALUES (:user_email, :description, :amount, :type, :category, :date)");
        
        $stmt->execute([
            ':user_email' => $data['user_email'] ?? 'default@expenso.com',
            ':description' => $data['description'],
            ':amount' => $data['amount'],
            ':type' => $data['type'],
            ':category' => $data['category'],
            ':date' => $data['date']
        ]);

        echo json_encode(["status" => "success", "message" => "Transaction saved successfully!"]);
    } catch (PDOException $e) {
        echo json_encode(["status" => "error", "message" => $e->getMessage()]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Invalid transaction data received."]);
}
?>