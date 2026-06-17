<?php
require_once '../../db.php';

header('Content-Type: application/json');

if (!isAdmin()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $order = isset($data['order']) && is_array($data['order']) ? $data['order'] : [];

    if (!empty($order)) {
        $pdo->beginTransaction();
        try {
            foreach ($order as $sort_order => $img_id) {
                $stmt = $pdo->prepare("UPDATE product_images SET sort_order = ? WHERE id = ?");
                $stmt->execute([(int)$sort_order, (int)$img_id]);
            }
            $pdo->commit();
            echo json_encode(['success' => true]);
            exit;
        } catch (Exception $e) {
            $pdo->rollBack();
            echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
            exit;
        }
    }
}

echo json_encode(['success' => false, 'message' => 'Invalid request']);
exit;
