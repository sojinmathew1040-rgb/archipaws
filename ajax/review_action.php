<?php
require_once '../db.php';
header('Content-Type: application/json');

$action = $_POST['action'] ?? '';
// User ID if logged in as customer
$user_id = $_SESSION['user_id'] ?? 0;

if (!$user_id && in_array($action, ['add_review', 'delete_review', 'edit_review'])) {
    echo json_encode(['success' => false, 'message' => 'Please log in first.']);
    exit;
}

if ($action === 'add_review') {
    $product_id = (int) ($_POST['product_id'] ?? 0);
    $rating = (int) ($_POST['rating'] ?? 5);
    $review_text = trim($_POST['review_text'] ?? '');

    if (!$product_id || !$review_text) {
        echo json_encode(['success' => false, 'message' => 'Product ID and review text are required.']);
        exit;
    }

    $photo_path = null;
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] == UPLOAD_ERR_OK) {
        $upload_dir = '../uploads/reviews/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }

        $ext = strtolower(pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'webp'];
        if (in_array($ext, $allowed)) {
            $filename = uniqid('review_') . '.' . $ext;
            $dest = $upload_dir . $filename;
            if (move_uploaded_file($_FILES['photo']['tmp_name'], $dest)) {
                $photo_path = 'uploads/reviews/' . $filename;
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Invalid image format.']);
            exit;
        }
    }

    try {
        $stmt = $pdo->prepare("INSERT INTO product_reviews (product_id, user_id, rating, review_text, photo_path) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$product_id, $user_id, $rating, $review_text, $photo_path]);
        echo json_encode(['success' => true]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }

} elseif ($action === 'delete_review') {
    $review_id = (int) ($_POST['review_id'] ?? 0);
    try {
        $stmt = $pdo->prepare("SELECT user_id, photo_path FROM product_reviews WHERE id = ?");
        $stmt->execute([$review_id]);
        $review = $stmt->fetch();

        if ($review && $review['user_id'] == $user_id) {
            $pdo->prepare("DELETE FROM product_reviews WHERE id = ?")->execute([$review_id]);
            if ($review['photo_path'] && file_exists('../' . $review['photo_path'])) {
                unlink('../' . $review['photo_path']);
            }
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
        }
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error']);
    }

} elseif ($action === 'edit_review') {
    $review_id = (int) ($_POST['review_id'] ?? 0);
    $rating = (int) ($_POST['rating'] ?? 5);
    $review_text = trim($_POST['review_text'] ?? '');

    try {
        $stmt = $pdo->prepare("SELECT user_id FROM product_reviews WHERE id = ?");
        $stmt->execute([$review_id]);
        if ($stmt->fetchColumn() == $user_id) {
            $pdo->prepare("UPDATE product_reviews SET rating = ?, review_text = ? WHERE id = ?")
                ->execute([$rating, $review_text, $review_id]);
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
        }
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error']);
    }

} elseif ($action === 'admin_reply') {
    if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
        echo json_encode(['success' => false, 'message' => 'Admin Unauthorized']);
        exit;
    }

    $review_id = (int) ($_POST['review_id'] ?? 0);
    $reply_text = trim($_POST['reply_text'] ?? '');

    try {
        $pdo->prepare("UPDATE product_reviews SET admin_reply = ? WHERE id = ?")->execute([$reply_text, $review_id]);
        echo json_encode(['success' => true]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid action']);
}
?>