<?php
session_start();
require_once "../includes/db.php";
require_once "../includes/auth.php";

header('Content-Type: application/json');

requireLogin();

$orderId = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;
if ($orderId <= 0) {
    echo json_encode(['success' => false, 'error' => 'Invalid order']);
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ? AND user_id = ? LIMIT 1");
$stmt->execute([$orderId, $_SESSION['user_id']]);
$order = $stmt->fetch();

if (!$order) {
    echo json_encode(['success' => false, 'error' => 'Order not found']);
    exit;
}

$itemsStmt = $pdo->prepare("SELECT oi.quantity, oi.price, p.name, p.image_url
                            FROM order_items oi
                            JOIN products p ON oi.product_id = p.id
                            WHERE oi.order_id = ?");
$itemsStmt->execute([$orderId]);
$items = $itemsStmt->fetchAll();

echo json_encode([
    'success' => true,
    'order' => [
        'id' => $order['id'],
        'order_number' => $order['order_number'],
        'created_at' => $order['created_at'],
        'status' => $order['status'],
        'total_amount' => $order['total_amount'],
        'shipping_address' => $order['shipping_address'],
        'payment_method' => $order['payment_method'],
        'items' => $items
    ]
]);
exit;
?>


