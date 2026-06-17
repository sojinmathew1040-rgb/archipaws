<?php
require_once 'db.php';
include 'header.php';

$wishlist_items = $_SESSION['wishlist'] ?? [];
$wishlist_products = [];

if (count($wishlist_items) > 0) {
    $clean_items = array_values($wishlist_items);
    $placeholders = implode(',', array_fill(0, count($clean_items), '?'));
    $stmt = $pdo->prepare("
        SELECT p.*, 
        (SELECT image_path FROM product_images WHERE product_id = p.id ORDER BY sort_order ASC LIMIT 1) as main_image 
        FROM products p 
        WHERE p.id IN ($placeholders)
    ");
    $stmt->execute($clean_items);
    $wishlist_products = $stmt->fetchAll();
}
?>

<style>
    /* Apple-Level Pro style overrides for wishlist.php */
    body {
        background: #fbfbfd;
        padding-top: 64px;
    }

    .pro-wish-hero {
        text-align: center;
        padding: 80px 20px 40px;
    }

    .pro-wish-hero h1 {
        font-size: 56px;
        font-weight: 700;
        letter-spacing: -0.03em;
        color: #1d1d1f;
        margin-bottom: 10px;
    }

    .pro-wish-hero p {
        font-size: 21px;
        color: #86868b;
    }

    .pro-wish-wrap {
        max-width: 1400px;
        margin: 0 auto 100px;
        padding: 0 40px;
    }

    /* Product Grid exact copy of shop.php Apple style */
    .pro-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        gap: 30px;
    }

    .pro-card {
        background: #fff;
        padding: 30px;
        border-radius: 32px;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.03);
        transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1);
        cursor: pointer;
        position: relative;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
    }

    .pro-card:hover {
        transform: translateY(-8px) scale(1.01);
        box-shadow: 0 24px 64px rgba(0, 0, 0, 0.08);
    }

    .pro-image {
        height: 220px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 25px;
    }

    .pro-image img {
        max-width: 100%;
        max-height: 100%;
        object-fit: contain;
        transition: transform 0.4s ease;
    }

    .pro-card:hover .pro-image img {
        transform: scale(1.05);
    }

    .pro-info {
        text-align: center;
    }

    .pro-info h4 {
        font-size: 20px;
        font-weight: 600;
        color: #1d1d1f;
        margin-bottom: 8px;
        letter-spacing: -0.01em;
    }

    .pro-price {
        font-size: 17px;
        font-weight: 500;
        color: #86868b;
        margin-top: 10px;
    }

    .pro-add-btn {
        width: 100%;
        background: #f5f5f7;
        color: #1d1d1f;
        border: none;
        padding: 14px 0;
        border-radius: 980px;
        font-weight: 600;
        font-size: 15px;
        margin-top: 20px;
        transition: all 0.3s ease;
        cursor: pointer;
    }

    .pro-card:hover .pro-add-btn {
        background: #1d1d1f;
        color: #fff;
    }

    .pro-add-btn[disabled] {
        background: #f5f5f7;
        color: #ccc;
        cursor: not-allowed;
    }

    .wish-icon {
        position: absolute;
        top: 25px;
        right: 25px;
        z-index: 10;
        font-size: 20px;
        cursor: pointer;
        transition: transform 0.3s;
        opacity: 1;
        color: #ff3b30;
    }

    .wish-icon:hover {
        transform: scale(1.2);
    }

    .pro-empty-state {
        text-align: center;
        padding: 100px 20px;
    }

    .pro-empty-state h2 {
        font-size: 40px;
        font-weight: 700;
        color: #1d1d1f;
        margin-bottom: 20px;
        letter-spacing: -0.02em;
    }

    .pro-empty-state p {
        font-size: 19px;
        color: #86868b;
        margin-bottom: 40px;
    }

    .pro-empty-btn {
        padding: 16px 40px;
        background: #1d1d1f;
        color: #fff;
        border: none;
        border-radius: 980px;
        font-size: 17px;
        font-weight: 600;
        cursor: pointer;
        transition: 0.3s;
        text-decoration: none;
        display: inline-flex;
    }

    .pro-empty-btn:hover {
        background: #333336;
        transform: scale(0.98);
    }

    @media(max-width: 768px) {
        .pro-wish-wrap {
            padding: 0 20px;
        }

        .pro-wish-hero h1 {
            font-size: 40px;
        }
    }
</style>

<div class="pro-wish-hero">
    <h1>Your Wishlist.</h1>
    <p>Save your favorite items here for later.</p>
</div>

<div class="pro-wish-wrap">
    <?php if (empty($wishlist_products)): ?>
        <div class="pro-empty-state">
            <svg width="60" height="60" viewBox="0 0 24 24" fill="none" stroke="#d2d2d7" stroke-width="2"
                style="margin-bottom:20px;">
                <path
                    d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z">
                </path>
            </svg>
            <h2>Nothing saved yet.</h2>
            <p>Start exploring our premium collection and add your favorites.</p>
            <a href="shop.php" class="pro-empty-btn">Discover Products</a>
        </div>
    <?php else: ?>
        <div class="pro-grid">
            <?php foreach ($wishlist_products as $p): ?>
                <div class="pro-card" onclick="window.location='product.php?id=<?= $p['id'] ?>'">
                    <div class="wish-icon"
                        onclick="toggleWishlist(event, <?= $p['id'] ?>, this); setTimeout(()=>window.location.reload(), 200);">
                        ❤️</div>

                    <div class="pro-image">
                        <img src="<?= htmlspecialchars($p['main_image'] ?? 'assets/images/16.jpeg') ?>"
                            alt="<?= htmlspecialchars($p['title']) ?>">
                    </div>

                    <div class="pro-info">
                        <h4><?= htmlspecialchars($p['title']) ?></h4>
                        <div class="pro-price">₹<?= number_format($p['price'], 2) ?></div>
                        <button class="pro-add-btn" <?= $p['stock_status'] == 'Sold Out' ? 'disabled' : '' ?>
                            onclick="event.stopPropagation(); addToCart(<?= $p['id'] ?>)">
                            <?= $p['stock_status'] == 'Sold Out' ? 'Out of Stock' : 'Move to Bag' ?>
                        </button>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php include 'footer.php'; ?>