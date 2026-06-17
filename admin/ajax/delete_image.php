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

    if ($img_id > 0) {
        $stmt = $pdo->prepare("SELECT image_path FROM product_images WHERE id = ?");
        $stmt->execute([$img_id]);
        $img = $stmt->fetch();
        if ($img) {
            $file_path = '../../' . $img['image_path'];
            if (file_exists($file_path) && is_file($file_path)) {
                unlink($file_path);
            }
            $pdo->prepare("DELETE FROM product_images WHERE id = ?")->execute([$img_id]);
            echo json_encode(['success' => true]);
            exit;
        }
    }
}

echo json_encode(['success' => false, 'message' => 'Invalid request']);
exit;
