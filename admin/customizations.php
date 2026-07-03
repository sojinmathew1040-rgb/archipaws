<?php
require_once '../db.php';
if (!isAdmin()) {
    header('Location: login.php');
    exit;
}

$message = '';
$error = '';

// Handle Status Update
if (isset($_POST['update_status'])) {
    $cust_id = (int)$_POST['customization_id'];
    $new_status = trim($_POST['status']);
    
    if (in_array($new_status, ['Pending', 'Contacted', 'In Progress', 'Completed'])) {
        try {
            $stmt = $pdo->prepare("UPDATE product_customizations SET status = ? WHERE id = ?");
            $stmt->execute([$new_status, $cust_id]);
            $message = "Status updated successfully!";
        } catch (PDOException $e) {
            $error = "Error updating status: " . $e->getMessage();
        }
    } else {
        $error = "Invalid status value.";
    }
}

// Handle Delete Request
if (isset($_POST['delete_customization'])) {
    $cust_id = (int)$_POST['customization_id'];
    try {
        $stmt = $pdo->prepare("DELETE FROM product_customizations WHERE id = ?");
        $stmt->execute([$cust_id]);
        $message = "Customization request deleted successfully!";
    } catch (PDOException $e) {
        $error = "Error deleting request: " . $e->getMessage();
    }
}

// Fetch Customization Requests
try {
    $stmt = $pdo->query("SELECT c.*, p.title AS product_title FROM product_customizations c JOIN products p ON c.product_id = p.id ORDER BY c.created_at DESC");
    $customizations = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $customizations = [];
    $error = "Error fetching customizations: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html>

<head>
    <title>Manage Customizations - Archipaws Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Quicksand:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/admin.css">
    <style>
        .badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 600;
        }
        .badge-pending { background: #fff8e5; color: #ff9500; }
        .badge-contacted { background: #eef3fc; color: #007aff; }
        .badge-inprogress { background: #f0f7f4; color: #34c759; }
        .badge-completed { background: #eaeaea; color: #666; }
        
        .status-form {
            display: inline-flex;
            gap: 5px;
            align-items: center;
        }
        .status-select {
            padding: 5px 8px;
            border-radius: 6px;
            border: 1px solid #ddd;
            font-size: 13px;
            background: #fff;
            outline: none;
        }
        .status-select:focus {
            border-color: #d6a86c;
        }
        
        .alert {
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
            font-weight: 500;
        }
        .alert-success { background: #eefaf0; color: #34c759; border: 1px solid rgba(52, 199, 89, 0.2); }
        .alert-error { background: #ffeeee; color: #ff3b30; border: 1px solid rgba(255, 59, 48, 0.2); }
        
        .cust-details-text {
            max-width: 300px;
            white-space: pre-wrap;
            word-wrap: break-word;
            font-size: 13px;
            color: #555;
            background: #fdfdfd;
            padding: 8px;
            border-radius: 6px;
            border: 1px solid #f0f0f0;
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
                <li><a href="sales_report.php">Sales Report</a></li>
                <li><a href="review_manager.php">Reviews</a></li>
                <li><a href="customizations.php" class="active">Customizations</a></li>
                <li><a href="logout.php">Logout</a></li>
            </ul>
        </div>
        <div class="main-content">
            <div class="header-top">
                <h1>Product Customizations</h1>
            </div>

            <?php if ($message): ?>
                <div class="alert alert-success"><?= htmlspecialchars($message) ?></div>
            <?php endif; ?>
            <?php if ($error): ?>
                <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <div class="card">
                <h3>Customization Requests</h3>
                <p style="color:#666; margin-top:5px; font-size: 14px; margin-bottom: 20px;">Review and manage customization requirements requested by store visitors and registered users.</p>
                
                <?php if (!empty($customizations)): ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Product</th>
                                <th>Customer Contact Details</th>
                                <th>Requested Needs</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($customizations as $cust): ?>
                                <?php
                                    $badge_class = 'badge-pending';
                                    if ($cust['status'] === 'Contacted') $badge_class = 'badge-contacted';
                                    elseif ($cust['status'] === 'In Progress') $badge_class = 'badge-inprogress';
                                    elseif ($cust['status'] === 'Completed') $badge_class = 'badge-completed';
                                ?>
                                <tr>
                                    <td style="font-size: 13px; color: #777;">
                                        <?= date('Y-m-d H:i', strtotime($cust['created_at'])) ?>
                                    </td>
                                    <td style="font-weight: 600; font-size: 14px;">
                                        <a href="../product.php?id=<?= $cust['product_id'] ?>" target="_blank" style="color: #1d1d1f; text-decoration: none;">
                                            <?= htmlspecialchars($cust['product_title']) ?>
                                        </a>
                                    </td>
                                    <td>
                                        <div style="font-size: 14px; font-weight: 500;"><?= htmlspecialchars($cust['customer_name']) ?></div>
                                        <div style="font-size: 12px; color: #555; margin-top: 2px;">✉️ <?= htmlspecialchars($cust['customer_email']) ?></div>
                                        <div style="font-size: 12px; color: #555; margin-top: 2px;">📞 <?= htmlspecialchars($cust['customer_phone']) ?></div>
                                        <?php if ($cust['user_id']): ?>
                                            <span style="font-size: 10px; background: #e1f5fe; color: #0288d1; padding: 2px 6px; border-radius: 4px; display: inline-block; margin-top: 4px; font-weight: 600;">Registered Customer</span>
                                        <?php else: ?>
                                            <span style="font-size: 10px; background: #eceff1; color: #37474f; padding: 2px 6px; border-radius: 4px; display: inline-block; margin-top: 4px; font-weight: 600;">Guest User</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="cust-details-text"><?= htmlspecialchars($cust['customization_details']) ?></div>
                                    </td>
                                    <td>
                                        <span class="badge <?= $badge_class ?>"><?= htmlspecialchars($cust['status']) ?></span>
                                    </td>
                                    <td>
                                        <div style="display: flex; flex-direction: column; gap: 8px;">
                                            <!-- Status Update Form -->
                                            <form method="POST" class="status-form">
                                                <input type="hidden" name="customization_id" value="<?= $cust['id'] ?>">
                                                <select name="status" class="status-select" onchange="this.form.submit()">
                                                    <option value="Pending" <?= $cust['status'] === 'Pending' ? 'selected' : '' ?>>Pending</option>
                                                    <option value="Contacted" <?= $cust['status'] === 'Contacted' ? 'selected' : '' ?>>Contacted</option>
                                                    <option value="In Progress" <?= $cust['status'] === 'In Progress' ? 'selected' : '' ?>>In Progress</option>
                                                    <option value="Completed" <?= $cust['status'] === 'Completed' ? 'selected' : '' ?>>Completed</option>
                                                </select>
                                                <input type="hidden" name="update_status" value="1">
                                            </form>
                                            
                                            <!-- Delete Button -->
                                            <form method="POST" onsubmit="return confirm('Are you sure you want to delete this request?');">
                                                <input type="hidden" name="customization_id" value="<?= $cust['id'] ?>">
                                                <input type="hidden" name="delete_customization" value="1">
                                                <button type="submit" class="btn-danger" style="width: 100%; text-align: center;">Delete</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p style="color: #777; font-size: 14px; text-align: center; padding: 40px 0;">No customization requests found.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>

</html>
