<?php
session_start();
require_once "../includes/db.php";
require_once "../includes/UserAddress.php";
require_once "../includes/auth.php";

requireLogin();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid JSON']);
    exit;
}

$user_id = $_SESSION['user_id'];
$title = trim($input['title'] ?? '');
$full_name = trim($input['full_name'] ?? '');
$street_address = trim($input['street_address'] ?? '');
$city = trim($input['city'] ?? '');
$phone = trim($input['phone'] ?? '');
$is_default = $input['is_default'] ?? false;

if (empty($title) || empty($full_name) || empty($street_address) || empty($city) || empty($phone)) {
    http_response_code(400);
    echo json_encode(['error' => 'All fields are required']);
    exit;
}

try {
    $userAddress = new UserAddress($pdo);
    $result = $userAddress->addAddress($user_id, $title, $full_name, $street_address, $city, $phone, $is_default);
    
    if ($result) {
        echo json_encode(['success' => true, 'message' => 'Address saved successfully']);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to save address']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
?>
