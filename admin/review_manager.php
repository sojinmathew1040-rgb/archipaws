<?php
require_once '../db.php';
if (!isAdmin()) {
    header('Location: login.php');
    exit;
}

$stmt = $pdo->query("SELECT r.*, p.title as product_title, u.name 
                     FROM product_reviews r 
                     JOIN products p ON r.product_id = p.id 
                     JOIN users u ON r.user_id = u.id 
                     ORDER BY r.created_at DESC");
$reviews = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html>

<head>
    <title>Manage Reviews - Archipaws Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Quicksand:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/admin.css">
    <style>
        .review-card {
            background: #fff;
            padding: 20px;
            border-radius: 12px;
            margin-bottom: 20px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.02);
            border: 1px solid #eee;
        }

        .review-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
        }

        .review-rating {
            color: #d6a86c;
        }

        .review-photo {
            max-width: 150px;
            border-radius: 8px;
            margin-top: 10px;
            display: block;
        }

        .reply-form {
            margin-top: 15px;
            background: #fbfbfd;
            padding: 15px;
            border-radius: 8px;
        }

        .reply-form textarea {
            width: 100%;
            padding: 10px;
            border-radius: 8px;
            border: 1px solid #ddd;
            margin-bottom: 10px;
            resize: vertical;
            font-family: inherit;
        }

        .btn {
            padding: 8px 16px;
            border-radius: 8px;
            background: #1d1d1f;
            color: #fff;
            border: none;
            cursor: pointer;
            font-weight: 600;
        }

        .btn:hover {
            background: #333;
        }
    </style>
</head>

<body>
    <div class="admin-container">
        <div class="sidebar">
            <h2>🐶 ARCHIPAWS Pro</h2>
            <ul>
                <li><a href="index.php">Dashboard</a></li>
                <li><a href="hero_manager.php">Hero Slider</a></li>
                <li><a href="category_manager.php">Categories</a></li>
                <li><a href="product_manager.php">Products</a></li>
                <li><a href="deal_manager.php">Deal Of The Day</a></li>
                <li><a href="testimonial_manager.php">Testimonials</a></li>
                <li><a href="orders.php">Orders</a></li>
                <li><a href="review_manager.php" class="active">Reviews</a></li>
                <li><a href="customizations.php">Customizations</a></li>
                <li><a href="logout.php">Logout</a></li>
            </ul>
        </div>
        <div class="main-content">
            <div class="header-top">
                <h1>Manage Product Reviews</h1>
            </div>

            <div id="msg"></div>

            <?php if (count($reviews) > 0): ?>
                <?php foreach ($reviews as $rev): ?>
                    <div class="review-card">
                        <div class="review-header">
                            <div>
                                <strong><?= htmlspecialchars($rev['name']) ?></strong> on
                                <em><?= htmlspecialchars($rev['product_title']) ?></em>
                            </div>
                            <div style="color: #888; font-size: 14px;">
                                <?= date('M j, Y g:i A', strtotime($rev['created_at'])) ?>
                            </div>
                        </div>
                        <div class="review-rating">
                            <?= str_repeat('★', $rev['rating']) . str_repeat('☆', 5 - $rev['rating']) ?>
                        </div>
                        <p style="margin: 10px 0;"><?= nl2br(htmlspecialchars($rev['review_text'])) ?></p>

                        <?php if ($rev['photo_path']): ?>
                            <a href="../<?= htmlspecialchars($rev['photo_path']) ?>" target="_blank">
                                <img src="../<?= htmlspecialchars($rev['photo_path']) ?>" class="review-photo">
                            </a>
                        <?php endif; ?>

                        <div class="reply-form">
                            <strong>Admin Reply:</strong>
                            <form onsubmit="submitReply(event, <?= $rev['id'] ?>)">
                                <textarea id="reply-<?= $rev['id'] ?>" rows="3"
                                    placeholder="Write an official response..."><?= htmlspecialchars($rev['admin_reply'] ?? '') ?></textarea>
                                <button type="submit" class="btn">Save Reply</button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="card">
                    <p>No reviews found.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        async function submitReply(e, reviewId) {
            e.preventDefault();
            const text = document.getElementById('reply-' + reviewId).value;
            const fd = new FormData();
            fd.append('action', 'admin_reply');
            fd.append('review_id', reviewId);
            fd.append('reply_text', text);

            try {
                const res = await fetch('../ajax/review_action.php', { method: 'POST', body: fd });
                const data = await res.json();
                const msgEl = document.getElementById('msg');
                if (data.success) {
                    msgEl.innerHTML = `<div style="padding: 15px; background: #eefaf0; color: #34c759; border-radius: 8px; margin-bottom: 20px;">Reply saved successfully!</div>`;
                    setTimeout(() => msgEl.innerHTML = '', 3000);
                } else {
                    alert(data.message || 'Error saving reply');
                }
            } catch (err) {
                alert('Network error');
            }
        }
    </script>
</body>

</html>