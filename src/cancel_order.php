<?php
session_start();
require_once "../includes/db.php";
require_once "../includes/auth.php";

header('Content-Type: application/json');

requireLogin();

$input = json_decode(file_get_contents('php://input'), true);
$orderId = isset($input['order_id']) ? intval($input['order_id']) : 0;

if ($orderId <= 0) {
    echo json_encode(['success' => false, 'error' => 'Invalid order']);
    exit;
}

$stmt = $pdo->prepare("SELECT id, status FROM orders WHERE id = ? AND user_id = ? LIMIT 1");
$stmt->execute([$orderId, $_SESSION['user_id']]);
$order = $stmt->fetch();

if (!$order) {
    echo json_encode(['success' => false, 'error' => 'Order not found']);
    exit;
}

if ($order['status'] !== 'pending') {
    echo json_encode(['success' => false, 'error' => 'Only pending orders can be cancelled']);
    exit;
}

$up = $pdo->prepare("UPDATE orders SET status = 'cancelled' WHERE id = ?");
$ok = $up->execute([$orderId]);

echo json_encode(['success' => $ok]);
exit;
?>


