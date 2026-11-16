<?php
require_once 'db.php';
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");

$action = $_GET['action'] ?? '';
$data = json_decode(file_get_contents("php://input"), true);

if ($action === 'get') {
    $email = $_GET['email'] ?? '';
    $stmt = $db->prepare("SELECT * FROM todos WHERE user_email = ? ORDER BY id DESC");
    $stmt->execute([$email]);
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
} 
elseif ($action === 'add' && !empty($data['user_email']) && !empty($data['task_text'])) {
    $stmt = $db->prepare("INSERT INTO todos (user_email, task_text, is_completed) VALUES (?, ?, 0)");
    $stmt->execute([$data['user_email'], $data['task_text']]);
    echo json_encode(["status" => "success"]);
} 
elseif ($action === 'toggle' && isset($data['id'])) {
    $stmt = $db->prepare("UPDATE todos SET is_completed = ? WHERE id = ?");
    $stmt->execute([$data['is_completed'], $data['id']]);
    echo json_encode(["status" => "success"]);
} 
elseif ($action === 'delete' && isset($data['id'])) {
    $stmt = $db->prepare("DELETE FROM todos WHERE id = ?");
    $stmt->execute([$data['id']]);
    echo json_encode(["status" => "success"]);
}
?>