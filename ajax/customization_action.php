<?php
require_once '../db.php';
header('Content-Type: application/json');

$action = $_POST['action'] ?? '';
$user_id = $_SESSION['user_id'] ?? null;

if ($action === 'submit_customization') {
    $product_id = (int) ($_POST['product_id'] ?? 0);
    $customer_name = trim($_POST['customer_name'] ?? '');
    $customer_email = trim($_POST['customer_email'] ?? '');
    $customer_phone = trim($_POST['customer_phone'] ?? '');
    $customization_details = trim($_POST['customization_details'] ?? '');

    if (!$product_id) {
        echo json_encode(['success' => false, 'message' => 'Product ID is required.']);
        exit;
    }
    if (!$customer_name || !$customer_email || !$customer_phone || !$customization_details) {
        echo json_encode(['success' => false, 'message' => 'All fields (Name, Email, Phone, Customization Details) are required.']);
        exit;
    }

    if (!filter_var($customer_email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'message' => 'Invalid email address format.']);
        exit;
    }

    try {
        // Insert customization record
        $stmt = $pdo->prepare("INSERT INTO product_customizations (product_id, user_id, customer_name, customer_email, customer_phone, customization_details, status) VALUES (?, ?, ?, ?, ?, ?, 'Pending')");
        $stmt->execute([$product_id, $user_id, $customer_name, $customer_email, $customer_phone, $customization_details]);
        
        echo json_encode(['success' => true, 'message' => 'Customization request submitted successfully!']);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid action.']);
}
?>
