<?php
require_once '../../db.php';

header('Content-Type: application/json');

if (!isAdmin()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $img_id = isset($data['id']) ? (int) $data['id'] : 0;
    $color_value = isset($data['color_value']) ? trim($data['color_value']) : '';

    if ($img_id > 0) {
        // Empty string means NULL / general image
        $color = ($color_value === '') ? null : $color_value;
        $stmt = $pdo->prepare("UPDATE product_images SET color_value = ? WHERE id = ?");
        $stmt->execute([$color, $img_id]);
        echo json_encode(['success' => true]);
        exit;
    }
}

echo json_encode(['success' => false, 'message' => 'Invalid request']);
exit;
