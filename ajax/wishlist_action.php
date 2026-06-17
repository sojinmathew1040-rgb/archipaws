<?php
session_start();
header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);
$product_id = isset($input['product_id']) ? (int) $input['product_id'] : 0;

if ($product_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid product']);
    exit;
}

if (!isset($_SESSION['wishlist'])) {
    $_SESSION['wishlist'] = [];
}

$in_wishlist = false;
$index = array_search($product_id, $_SESSION['wishlist']);

if ($index !== false) {
    // Remove
    array_splice($_SESSION['wishlist'], $index, 1);
} else {
    // Add
    $_SESSION['wishlist'][] = $product_id;
    $in_wishlist = true;
}

echo json_encode([
    'success' => true,
    'in_wishlist' => $in_wishlist,
    'count' => count($_SESSION['wishlist'])
]);
