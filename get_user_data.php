<?php
require_once 'db.php';
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");

$email = $_GET['email'] ?? '';

if (!empty($email)) {
    // Fetch settings
    $stmtSettings = $db->prepare("SELECT * FROM settings WHERE email = ?");
    $stmtSettings->execute([$email]);
    $settings = $stmtSettings->fetch(PDO::FETCH_ASSOC);

    // Fetch transactions
    $stmtTx = $db->prepare("SELECT * FROM transactions WHERE user_email = ? ORDER BY id DESC");
    $stmtTx->execute([$email]);
    $transactions = $stmtTx->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        "settings" => $settings ?: null,
        "transactions" => $transactions ?: []
    ]);
}
?>