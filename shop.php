<?php
require_once 'db.php';
require_once 'category_helper.php';
include 'header.php';

$shop_products = $pdo->query("
    SELECT p.*, 
    (SELECT image_path FROM product_images WHERE product_id = p.id ORDER BY sort_order ASC LIMIT 1) as main_image 
    FROM products p 
    ORDER BY id DESC
")->fetchAll();

$categories = $pdo->query("SELECT * FROM categories ORDER BY sort_order ASC")->fetchAll();
$wishlist_items = $_SESSION['wishlist'] ?? [];
$requested_cat = $_GET['category'] ?? 'all';
?>

<style>
    /* Apple-Level Pro style overrides for shop.php */
    body {
        background: #fbfbfd;
        padding-top: 64px;
    }

    .shop-hero {
        text-align: center;
        padding: 80px 20px 40px;
    }

    .shop-hero h1 {
        font-size: 56px;
        font-weight: 700;
        letter-spacing: -0.03em;
        color: #1d1d1f;
        margin-bottom: 10px;
    }

    .shop-hero p {
        font-size: 21px;
        font-weight: 500;
        color: #86868b;
        letter-spacing: -0.01em;
    }

    .shop-wrapper {
        max-width: 1400px;
        margin: 40px auto 80px auto;
        padding: 0 40px;
        display: flex;
        gap: 60px;
        align-items: flex-start;
    }

    .sidebar {
        width: 260px;
        flex-shrink: 0;
        position: sticky;
        top: 100px;
    }

    .sidebar h3 {
        font-size: 19px;
        font-weight: 600;
        color: #1d1d1f;
        margin-bottom: 20px;
        letter-spacing: -0.01em;
    }

    .filter-group {
        display: flex;
        flex-direction: column;
        gap: 14px;
        margin-bottom: 40px;
    }

    /* Custom Checkmark matching Apple forms */
    .custom-checkbox {
        position: relative;
        padding-left: 32px;
        cursor: pointer;
        font-size: 15px;
        font-weight: 500;
        color: #86868b;
        transition: color 0.3s;
        user-select: none;
        display: flex;
        align-items: center;
    }

    .custom-checkbox:hover {
        color: #1d1d1f;
    }

    .custom-checkbox input {
        position: absolute;
        opacity: 0;
        cursor: pointer;
        height: 0;
        width: 0;
    }

    .checkmark {
        position: absolute;
        left: 0;
        top: 50%;
        transform: translateY(-50%);
        height: 20px;
        width: 20px;
        background-color: #fff;
        border: 1px solid rgba(0, 0, 0, 0.1);
        border-radius: 6px;
        box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.02);
        transition: all 0.2s ease;
    }

    .custom-checkbox input:checked~.checkmark {
        background-color: #1d1d1f;
        border-color: #1d1d1f;
        box-shadow: none;
    }

    .custom-checkbox input:checked~.checkmark:after {
        display: block;
    }

    .checkmark:after {
        content: "";
        position: absolute;
        display: none;
        left: 7px;
        top: 3px;
        width: 4px;
        height: 9px;
        border: solid white;
        border-width: 0 2px 2px 0;
        transform: rotate(45deg);
    }

    .shop-content {
        flex: 1;
        min-width: 0;
    }

    .product-grid {
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

    .pro-info .price {
        font-size: 17px;
        font-weight: 500;
        color: #86868b;
    }

    .pro-info .old-price {
        text-decoration: line-through;
        margin-right: 8px;
        color: #ccc;
    }

    .add-btn {
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

    .pro-card:hover .add-btn {
        background: #1d1d1f;
        color: #fff;
    }

    .add-btn[disabled] {
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
        opacity: 0.8;
    }

    .wish-icon:hover {
        transform: scale(1.2);
        opacity: 1;
    }

    .wish-icon.active {
        color: #ff3b30;
        opacity: 1;
    }

    .pro-badge {
        position: absolute;
        top: 25px;
        left: 25px;
        background: #eefaf0;
        color: #28a745;
        padding: 6px 12px;
        border-radius: 8px;
        font-size: 12px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }

    .sidebar-header-mobile {
        display: none !important;
    }

    .catalog-search input:focus {
        border-color: #1d1d1f !important;
        background: #fff !important;
        box-shadow: 0 0 0 4px rgba(0, 0, 0, 0.05);
    }

    @media(max-width: 768px) {
        .shop-wrapper {
            flex-direction: column;
            padding: 0 20px;
            gap: 20px;
        }

        .sidebar-header-mobile {
            display: flex !important;
        }

        .mobile-filter-trigger-inline {
            display: inline-block !important;
        }

        .desktop-filter-status {
            width: 100%;
            justify-content: space-between;
        }

        /* Turn sidebar into bottom drawer on mobile */
        .sidebar {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.4);
            backdrop-filter: blur(8px);
            -webkit-backdrop-filter: blur(8px);
            z-index: 9999;
            align-items: flex-end;
            justify-content: center;
        }

        .sidebar.active {
            display: flex;
        }

        .sidebar-content-wrapper {
            background: #fff;
            width: 100%;
            max-height: 80vh;
            border-radius: 32px 32px 0 0;
            padding: 30px 24px 40px 24px;
            overflow-y: auto;
            box-shadow: 0 -10px 40px rgba(0, 0, 0, 0.15);
            animation: slideUp 0.3s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }

        @keyframes slideUp {
            from {
                transform: translateY(100%);
            }

            to {
                transform: translateY(0);
            }
        }

        .shop-hero h1 {
            font-size: 36px;
        }
    }
    
    .filter-mini-svg {
        display: inline-flex;
        align-items: center;
        margin-right: 8px;
        vertical-align: middle;
    }
    .filter-mini-svg svg {
        stroke-width: 4.5px;
    }
</style>

<div class="shop-hero">
    <div
        style="display: inline-flex; align-items: center; justify-content: center; gap: 12px; background: rgba(0,0,0,0.03); padding: 8px 20px; border-radius: 100px; font-size: 13px; font-weight: 500; letter-spacing: 0.02em; margin-bottom: 24px; border: 1px solid rgba(0,0,0,0.05); user-select: none;">
        <a href="index.php"
            style="color: #86868b; text-decoration: none; display: flex; align-items: center; transition: color 0.3s;"
            onmouseover="this.style.color='#1d1d1f'" onmouseout="this.style.color='#86868b'">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                stroke-linecap="round" stroke-linejoin="round" style="margin-right: 6px;">
                <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
                <polyline points="9 22 9 12 15 12 15 22"></polyline>
            </svg>
            Home
        </a>
        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="#d2d2d7" stroke-width="2"
            stroke-linecap="round" stroke-linejoin="round">
            <polyline points="9 18 15 12 9 6"></polyline>
        </svg>
        <span style="color: #1d1d1f; font-weight: 600;">Shop</span>
    </div>
    <h1>Our Collection.</h1>
    <p>Discover the finest kennels and premium accessories.</p>
</div>

<div class="shop-wrapper">
    <aside class="sidebar" id="shopSidebar">
        <div class="sidebar-content-wrapper">
            <div class="sidebar-header-mobile"
                style="justify-content: space-between; align-items: center; margin-bottom: 25px; border-bottom: 1px solid rgba(0,0,0,0.05); padding-bottom: 15px;">
                <h3 style="margin: 0; font-size: 22px; font-weight: 700; color: #1d1d1f;">Filters & Sort</h3>
                <button onclick="toggleMobileFilter(false)"
                    style="background: rgba(0,0,0,0.05); border: none; font-size: 16px; font-weight: 600; cursor: pointer; color: #1d1d1f; width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center;">✕</button>
            </div>

            <h3 style="margin-top: 0;">Categories</h3>
            <div class="filter-group">
                <label class="custom-checkbox">
                    <input type="checkbox" class="cat-filter" value="all" <?= strtolower($requested_cat) === 'all' ? 'checked' : '' ?>>
                    <span class="checkmark"></span>All Products
                </label>
                <?php foreach ($categories as $c): ?>
                    <label class="custom-checkbox">
                        <input type="checkbox" class="cat-filter" value="<?= htmlspecialchars($c['name']) ?>"
                            <?= strtolower($requested_cat) === strtolower($c['name']) ? 'checked' : '' ?>>
                        <span class="checkmark"></span>
                        <span class="filter-mini-svg">
                            <?= render_category_icon($c, 18) ?>
                        </span>
                        <?= htmlspecialchars($c['name']) ?>
                    </label>
                <?php endforeach; ?>
            </div>

            <h3 style="margin-top: 30px;">Price Range</h3>
            <div class="filter-group">
                <label class="custom-checkbox"><input type="checkbox" class="price-filter" value="under-2000"><span
                        class="checkmark"></span>Under ₹2000</label>
                <label class="custom-checkbox"><input type="checkbox" class="price-filter" value="2000-5000"><span
                        class="checkmark"></span>₹2000 - ₹5000</label>
                <label class="custom-checkbox"><input type="checkbox" class="price-filter" value="5000-10000"><span
                        class="checkmark"></span>₹5000 - ₹10000</label>
                <label class="custom-checkbox"><input type="checkbox" class="price-filter" value="over-10000"><span
                        class="checkmark"></span>Over ₹10000</label>
            </div>
        </div>
    </aside>

    <div class="shop-content">
        <!-- Interactive Catalog Search Header -->
        <div class="catalog-header"
            style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 40px; gap: 20px; flex-wrap: wrap;">
            <div class="catalog-search" style="position: relative; flex: 1; max-width: 400px; min-width: 260px;">
                <input type="text" id="catalogSearchInput" placeholder="Search within 100,000+ catalog items..."
                    value="<?= htmlspecialchars($_GET['search'] ?? '') ?>"
                    style="width: 100%; padding: 14px 20px 14px 48px; border: 1px solid rgba(0,0,0,0.06); border-radius: 100px; outline: none; background: #f5f5f7; font-size: 14px; font-weight: 500; transition: all 0.3s ease;">
                <span
                    style="position: absolute; left: 18px; top: 50%; transform: translateY(-50%); color: #86868b; font-size: 16px;">🔍</span>
            </div>

            <div class="desktop-filter-status" style="display: flex; align-items: center; gap: 15px;">
                <span id="product-count-badge"
                    style="font-size: 14px; font-weight: 600; color: #86868b; font-family: -apple-system, sans-serif;">Showing
                    all products</span>
                <button onclick="toggleMobileFilter(true)" class="mobile-filter-trigger-inline"
                    style="display: none; background: #1d1d1f; border: none; padding: 12px 24px; border-radius: 100px; font-weight: 600; font-size: 13px; color: #fff; cursor: pointer; box-shadow: 0 4px 12px rgba(0,0,0,0.15); transition: 0.2s;">Filters
                    🔍</button>
            </div>
        </div>
        <div class="product-grid">
            <?php foreach ($shop_products as $p):
                $in_wishlist = in_array($p['id'], $wishlist_items);
                $price_cat = 'under-2000';
                if ($p['price'] >= 2000 && $p['price'] <= 5000)
                    $price_cat = '2000-5000';
                elseif ($p['price'] > 5000 && $p['price'] <= 10000)
                    $price_cat = '5000-10000';
                elseif ($p['price'] > 10000)
                    $price_cat = 'over-10000';
                ?>
                <div class="pro-card" data-cat="<?= htmlspecialchars($p['category']) ?>" data-price="<?= $price_cat ?>"
                    onclick="window.location='product.php?id=<?= $p['id'] ?>'">

                    <div class="wish-icon <?= $in_wishlist ? 'active' : '' ?>"
                        onclick="toggleWishlist(event, <?= $p['id'] ?>, this)">
                        <?= $in_wishlist ? '❤️' : '🤍' ?>
                    </div>
                    <?php if ($p['badge']): ?>
                        <span class="pro-badge"><?= htmlspecialchars($p['badge']) ?></span>
                    <?php endif; ?>

                    <div class="pro-image">
                        <img src="<?= htmlspecialchars($p['main_image'] ?? 'assets/images/16.jpeg') ?>"
                            alt="<?= htmlspecialchars($p['title']) ?>">
                    </div>

                    <div class="pro-info">
                        <h4><?= htmlspecialchars($p['title']) ?></h4>
                        <div class="rating" style="color: #d6a86c; font-size: 14px; margin-bottom: 8px;">
                            <?= str_repeat('★', $p['rating']) . str_repeat('☆', 5 - $p['rating']) ?>
                        </div>
                        <p class="price">
                            <?php if ($p['old_price']): ?><span
                                    class="old-price">₹<?= number_format($p['old_price'], 2) ?></span><?php endif; ?>
                            ₹<?= number_format($p['price'], 2) ?>
                        </p>
                        <button class="add-btn" <?= $p['stock_status'] == 'Sold Out' ? 'disabled' : '' ?>
                            onclick="event.stopPropagation(); addToCart(<?= $p['id'] ?>)">
                            <?= $p['stock_status'] == 'Sold Out' ? 'Sold Out' : 'Add to Bag' ?>
                        </button>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<script>
    function toggleMobileFilter(isOpen) {
        const sidebar = document.getElementById("shopSidebar");
        if (sidebar) {
            if (isOpen) {
                sidebar.classList.add("active");
                document.body.style.overflow = "hidden"; // Prevent background scrolling
            } else {
                sidebar.classList.remove("active");
                document.body.style.overflow = ""; // Enable scrolling
            }
        }
    }

    document.addEventListener("DOMContentLoaded", () => {
        const catFilters = document.querySelectorAll(".cat-filter");
        const priceFilters = document.querySelectorAll(".price-filter");
        const products = document.querySelectorAll(".pro-card");
        const searchInput = document.getElementById("catalogSearchInput");
        const countBadge = document.getElementById("product-count-badge");

        function filterProducts() {
            let activeCats = Array.from(catFilters).filter(cb => cb.checked).map(cb => cb.value);
            let activePrices = Array.from(priceFilters).filter(cb => cb.checked).map(cb => cb.value);
            const searchQuery = searchInput ? searchInput.value.toLowerCase().trim() : "";

            const showAllCats = activeCats.includes("all") || activeCats.length === 0;
            const ignorePrice = activePrices.length === 0;

            let visibleCount = 0;
            products.forEach(p => {
                const productCat = (p.getAttribute("data-cat") || "").toLowerCase();
                const productPrice = p.getAttribute("data-price") || "";
                const productTitle = (p.querySelector("h4")?.innerText || "").toLowerCase();

                let catMatch = showAllCats ? true : activeCats.some(cat => productCat === cat.toLowerCase());
                let priceMatch = ignorePrice ? true : activePrices.includes(productPrice);
                let searchMatch = searchQuery === "" ? true : productTitle.includes(searchQuery);

                if (catMatch && priceMatch && searchMatch) {
                    p.style.display = "flex";
                    visibleCount++;
                } else {
                    p.style.display = "none";
                }
            });

            if (countBadge) {
                countBadge.innerText = visibleCount === products.length
                    ? "Showing all products"
                    : `Showing ${visibleCount} of ${products.length} products`;
            }
        }

        // Initial filtering on page load to apply URL queries
        filterProducts();

        if (searchInput) {
            searchInput.addEventListener("input", filterProducts);
        }

        catFilters.forEach(cb => cb.addEventListener("change", function () {
            if (this.value === "all" && this.checked) {
                catFilters.forEach(f => { if (f.value !== "all") f.checked = false; });
            } else if (this.value !== "all" && this.checked) {
                const allFilter = document.querySelector(".cat-filter[value='all']");
                if (allFilter) allFilter.checked = false;
            }
            filterProducts();
        }));

        priceFilters.forEach(cb => cb.addEventListener("change", filterProducts));

        // Close sidebar if clicking outside the drawer content on mobile
        const sidebar = document.getElementById("shopSidebar");
        if (sidebar) {
            sidebar.addEventListener("click", function (e) {
                if (e.target === sidebar) {
                    toggleMobileFilter(false);
                }
            });
        }
    });
</script>

<?php include 'footer.php'; ?>