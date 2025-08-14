<?php
session_start();
require_once "../includes/db.php";
require_once "../includes/UserAddress.php";
require_once "../includes/auth.php";
require_once "../includes/csrf.php";

requireLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../profile?tab=addresses&error=invalid_request');
    exit;
}

if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
    header('Location: ../profile?tab=addresses&error=csrf_invalid');
    exit;
}

$user_id = $_SESSION['user_id'];
$action = $_POST['action'] ?? '';
$userAddress = new UserAddress($pdo);

try {
    switch ($action) {
        case 'add':
            $title = trim($_POST['title'] ?? '');
            $full_name = trim($_POST['full_name'] ?? '');
            $street_address = trim($_POST['street_address'] ?? '');
            $city = trim($_POST['city'] ?? '');
            $phone = trim($_POST['phone'] ?? '');
            $is_default = isset($_POST['is_default']) ? true : false;
            
            if (empty($title) || empty($full_name) || empty($street_address) || empty($city) || empty($phone)) {
                header('Location: ../profile?tab=addresses&error=missing_fields');
                exit;
            }
            
            $result = $userAddress->addAddress($user_id, $title, $full_name, $street_address, $city, $phone, $is_default);
            
            if ($result) {
                header('Location: ../profile?tab=addresses&success=address_added');
            } else {
                header('Location: ../profile?tab=addresses&error=add_failed');
            }
            break;
            
        case 'edit':
            $address_id = intval($_POST['address_id'] ?? 0);
            $title = trim($_POST['title'] ?? '');
            $full_name = trim($_POST['full_name'] ?? '');
            $street_address = trim($_POST['street_address'] ?? '');
            $city = trim($_POST['city'] ?? '');
            $phone = trim($_POST['phone'] ?? '');
            $is_default = isset($_POST['is_default']) ? true : false;
            
            if ($address_id <= 0 || empty($title) || empty($full_name) || empty($street_address) || empty($city) || empty($phone)) {
                header('Location: ../profile?tab=addresses&error=missing_fields');
                exit;
            }
            
            $result = $userAddress->updateAddress($address_id, $user_id, $title, $full_name, $street_address, $city, $phone, $is_default);
            
            if ($result) {
                header('Location: ../profile?tab=addresses&success=address_updated');
            } else {
                header('Location: ../profile?tab=addresses&error=update_failed');
            }
            break;
            
        case 'delete':
            $address_id = intval($_POST['address_id'] ?? 0);
            
            if ($address_id <= 0) {
                header('Location: ../profile?tab=addresses&error=invalid_address');
                exit;
            }
            
            $result = $userAddress->deleteAddress($address_id, $user_id);
            
            if ($result) {
                header('Location: ../profile?tab=addresses&success=address_deleted');
            } else {
                header('Location: ../profile?tab=addresses&error=delete_failed');
            }
            break;
            
        case 'set_default':
            $address_id = intval($_POST['address_id'] ?? 0);
            
            if ($address_id <= 0) {
                header('Location: ../profile?tab=addresses&error=invalid_address');
                exit;
            }
            
            $result = $userAddress->setDefaultAddress($address_id, $user_id);
            
            if ($result) {
                header('Location: ../profile?tab=addresses&success=default_set');
            } else {
                header('Location: ../profile?tab=addresses&error=default_failed');
            }
            break;
            
        default:
            header('Location: ../profile?tab=addresses&error=invalid_action');
            break;
    }
} catch (Exception $e) {
    error_log("Address management error: " . $e->getMessage());
    header('Location: ../profile?tab=addresses&error=system_error');
}

exit;
?>
