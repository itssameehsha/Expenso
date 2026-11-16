<?php
require_once 'db.php';

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

$method = $_SERVER['REQUEST_METHOD'];
$request = isset($_GET['action']) ? $_GET['action'] : '';

if ($method === 'GET') {
    if ($request === 'transactions') {
        $stmt = $db->query("SELECT * FROM transactions ORDER BY id DESC");
        $transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($transactions);
    } 
    else if ($request === 'settings') {
        $stmt = $db->query("SELECT * FROM settings ORDER BY id DESC LIMIT 1");
        $settings = $stmt->fetch(PDO::FETCH_ASSOC);
        echo json_encode($settings ? $settings : ["name" => "", "email" => "", "phone" => "", "monthly_budget" => 30000]);
    }
} 
else if ($method === 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);

    if ($request === 'transactions') {
        $stmt = $db->prepare("INSERT INTO transactions (description, category, amount, type, date) VALUES (:desc, :cat, :amt, :type, :date)");
        $stmt->execute([
            ':desc' => $data['description'],
            ':cat' => $data['category'],
            ':amt' => $data['amount'],
            ':type' => $data['type'],
            ':date' => $data['date']
        ]);
        echo json_encode(["status" => "success", "message" => "Transaction saved"]);
    } 
    else if ($request === 'settings') {
        $stmt = $db->prepare("INSERT INTO settings (name, email, phone, monthly_budget) VALUES (:name, :email, :phone, :budget)");
        $stmt->execute([
            ':name' => $data['name'],
            ':email' => $data['email'],
            ':phone' => $data['phone'],
            ':budget' => $data['monthly_budget']
        ]);
        echo json_encode(["status" => "success", "message" => "Settings saved"]);
    }
}
?>