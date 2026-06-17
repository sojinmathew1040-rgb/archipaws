<?php
session_start();
header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);
$product_id = isset($input['product_id']) ? (int) $input['product_id'] : 0;
$variation_id = isset($input['variation_id']) ? (int) $input['variation_id'] : 0;
$cart_key = isset($input['cart_key']) ? trim($input['cart_key']) : '';
$action = isset($input['action']) ? $input['action'] : 'add';
$qty = isset($input['qty']) ? (int) $input['qty'] : 1;

if ($cart_key === '') {
    if ($product_id <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid product']);
        exit;
    }
    $cart_key = (string)$product_id;
    if ($variation_id > 0) {
        $cart_key = $product_id . '_' . $variation_id;
    }
} else {
    $parts = explode('_', $cart_key);
    $product_id = (int)$parts[0];
    $variation_id = isset($parts[1]) ? (int)$parts[1] : 0;
}

if ($product_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid product']);
    exit;
}

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

if ($action === 'add') {
    if (isset($_SESSION['cart'][$cart_key])) {
        $_SESSION['cart'][$cart_key] += $qty;
    } else {
        $_SESSION['cart'][$cart_key] = $qty;
    }
} elseif ($action === 'update') {
    if ($qty > 0) {
        $_SESSION['cart'][$cart_key] = $qty;
    } else {
        unset($_SESSION['cart'][$cart_key]);
    }
} elseif ($action === 'remove') {
    unset($_SESSION['cart'][$cart_key]);
}

$total_count = array_sum(array_values($_SESSION['cart']));

echo json_encode([
    'success' => true,
    'count' => $total_count
]);

