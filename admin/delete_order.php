<?php
require_once '../db.php';

if (!isAdmin()) {
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['order_id'])) {
    $order_id = intval($_POST['order_id']);

    try {
        $pdo->beginTransaction();

        // First delete associated order items
        $stmt1 = $pdo->prepare("DELETE FROM order_items WHERE order_id = ?");
        $stmt1->execute([$order_id]);

        // Then delete the order itself
        $stmt2 = $pdo->prepare("DELETE FROM orders WHERE id = ?");
        $stmt2->execute([$order_id]);

        $pdo->commit();
    } catch (Exception $e) {
        $pdo->rollBack();
        // Ignore errors in production, or log them
    }
}

// Redirect back to orders page
header('Location: orders.php');
exit;
