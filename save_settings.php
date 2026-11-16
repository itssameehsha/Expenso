<?php
require_once 'db.php';
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");

$data = json_decode(file_get_contents("php://input"), true);

if ($data && !empty($data['email'])) {
    try {
        // UPSERT: Insert new user or update existing user settings if email already exists
        $stmt = $db->prepare("INSERT INTO settings (name, email, phone, monthly_budget) VALUES (:name, :email, :phone, :budget)
                              ON DUPLICATE KEY UPDATE name=:name, phone=:phone, monthly_budget=:budget");
        $stmt->execute([
            ':name' => $data['name'],
            ':email' => $data['email'],
            ':phone' => $data['phone'],
            ':budget' => $data['monthly_budget']
        ]);
        echo json_encode(["status" => "success"]);
    } catch (PDOException $e) {
        echo json_encode(["status" => "error", "message" => $e->getMessage()]);
    }
}
?>