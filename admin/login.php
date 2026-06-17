<?php
require_once '../db.php';
if (isAdmin()) {
    header('Location: index.php');
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    $stmt = $pdo->prepare("SELECT * FROM admins WHERE username = ?");
    $stmt->execute([$username]);
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($admin && password_verify($password, $admin['password'])) {
        session_regenerate_id(true);
        $_SESSION['admin_logged_in'] = true;
        header('Location: index.php');
        exit;
    } else {
        $error = 'Invalid credentials';
    }
}
?>
<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Archipaws Admin - Login</title>
    <link href="https://fonts.googleapis.com/css2?family=Quicksand:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/admin.css">
</head>

<body>
    <div class="login-wrapper">
        <div class="login-box">
            <h2>Admin Login</h2>
            <?php if ($error): ?>
                <p style="color:red; margin-bottom:15px; font-size:14px;"><?= $error ?></p><?php endif; ?>
            <form method="POST">
                <div class="form-group text-left">
                    <input type="text" name="username" placeholder="Username" required>
                </div>
                <div class="form-group text-left">
                    <input type="password" name="password" placeholder="Password" required>
                </div>
                <button type="submit" class="btn-primary"
                    style="width:100%; padding:15px; border-radius:15px; font-size:16px;">Sign In</button>
            </form>
        </div>
    </div>
</body>

</html>