<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$cart_count = isset($_SESSION['cart']) ? array_sum($_SESSION['cart']) : 0;
$wishlist_count = isset($_SESSION['wishlist']) ? count($_SESSION['wishlist']) : 0;
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Archipaws Pet Shop</title>

    <link rel="stylesheet" href="assets/css/style.css?v=<?= time() ?>">
    <link rel="stylesheet" href="assets/css/addons.css?v=<?= time() ?>">

    <!-- FONT -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Outfit:wght@400;600;800&display=swap"
        rel="stylesheet">

</head>

<body class="<?= (basename($_SERVER['PHP_SELF']) === 'index.php') ? 'home-page' : '' ?>">

    <!-- ===== HEADER ===== -->
    <header>

        <!-- TOP HEADER -->
        <div class="top-header">

            <div class="hamburger" onclick="toggleMenu()">☰</div>

            <a href="index.php" style="text-decoration:none; color:inherit;">
                <div class="logo">
                    🐶 <span>archipaws</span>
                    <small>Pet Shop</small>
                </div>
            </a>

            <div class="icons">
                <div class="icon-btn account-btn" onclick="window.location='account.php'" title="Account">
                    <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2"
                        stroke-linecap="round" stroke-linejoin="round">
                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" />
                        <circle cx="12" cy="7" r="4" />
                    </svg>
                </div>
                <div class="icon-btn wishlist-link" onclick="window.location='wishlist.php'" title="Wishlist">
                    <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2"
                        stroke-linecap="round" stroke-linejoin="round">
                        <path
                            d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z" />
                    </svg>
                    <span id="wishlist-count-badge" class="badge-count"
                        style="<?= $wishlist_count === 0 ? 'display: none;' : '' ?>"><?= $wishlist_count ?></span>
                </div>
                <div class="icon-btn cart" onclick="window.location='cart.php'" title="Cart">
                    <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2"
                        stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="9" cy="21" r="1" />
                        <circle cx="20" cy="21" r="1" />
                        <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6" />
                    </svg>
                    <span id="cart-count-badge" class="badge-count"
                        style="<?= $cart_count === 0 ? 'display: none;' : '' ?>"><?= $cart_count ?></span>
                </div>
            </div>

        </div>

        <!-- NAVBAR -->
        <?php $current_page = basename($_SERVER['PHP_SELF']); ?>
        <nav class="navbar">

            <ul class="menu" id="menu">
                <li><a href="index.php" class="<?= $current_page === 'index.php' ? 'active' : '' ?>">Home</a></li>
                <li><a href="about.php" class="<?= $current_page === 'about.php' ? 'active' : '' ?>">About Us</a></li>
                <li><a href="shop.php" class="<?= $current_page === 'shop.php' ? 'active' : '' ?>">Shop</a></li>
                <li><a href="contact.php" class="<?= $current_page === 'contact.php' ? 'active' : '' ?>">Contact</a>
                </li>
                <li class="mobile-menu-item"><a href="account.php"
                        class="<?= $current_page === 'account.php' ? 'active' : '' ?>">My Account</a></li>
                <li class="mobile-menu-item"><a href="wishlist.php"
                        class="<?= $current_page === 'wishlist.php' ? 'active' : '' ?>">Wishlist (<span
                            id="mobile-wishlist-count"><?= $wishlist_count ?></span>)</a></li>
                <li class="mobile-menu-item"><a href="cart.php"
                        class="<?= $current_page === 'cart.php' ? 'active' : '' ?>">Cart (<span
                            id="mobile-cart-count"><?= $cart_count ?></span>)</a></li>
            </ul>

        </nav>

    </header>