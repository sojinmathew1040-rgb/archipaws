<?php
require_once 'db.php';
require_once 'category_helper.php';
include 'header.php';

// Fetch hero slides
$hero_slides = $pdo->query("SELECT * FROM hero_slides ORDER BY sort_order ASC, id ASC")->fetchAll();
if (empty($hero_slides)) {
    $hero_slides = [['offer_text' => 'SAVE 10 - 20 % OFF', 'title_line1' => 'Best Destination', 'title_line2' => 'Your Pets', 'button_text' => 'SHOP NOW →', 'button_link' => 'shop.php', 'image_path' => 'assets/images/12.jpeg']];
}

// Fetch Categories
$home_categories = $pdo->query("SELECT * FROM categories ORDER BY sort_order ASC")->fetchAll();

// Fetch Deal of the Day
$deal_of_the_day = $pdo->query("
    SELECT d.end_time, p.*,
    (SELECT image_path FROM product_images WHERE product_id = p.id ORDER BY sort_order ASC LIMIT 1) as main_image 
    FROM deal_of_the_day d 
    JOIN products p ON d.product_id = p.id 
    WHERE d.end_time > NOW()
    LIMIT 1
")->fetch();

// Fetch Trending Products
$trending_products = $pdo->query("
    SELECT p.*, 
    (SELECT image_path FROM product_images WHERE product_id = p.id ORDER BY sort_order ASC LIMIT 1) as main_image 
    FROM products p 
    WHERE p.is_trending = 1
    ORDER BY id DESC LIMIT 4
")->fetchAll();

// Fetch Testimonials
$home_testimonials = $pdo->query("SELECT * FROM testimonials ORDER BY created_at DESC LIMIT 3")->fetchAll();

// Fetch products for home (up to 3)
$home_products = $pdo->query("
    SELECT p.*, 
    (SELECT image_path FROM product_images WHERE product_id = p.id ORDER BY sort_order ASC LIMIT 1) as main_image 
    FROM products p 
    ORDER BY id DESC LIMIT 3
")->fetchAll();

$wishlist_items = $_SESSION['wishlist'] ?? [];
?>

<section class="hero">

    <div class="hero-slider">

        <div class="slides">
            <?php foreach ($hero_slides as $index => $slide): ?>
                <div class="slide <?= $index === 0 ? 'active' : '' ?>">
                    <?php
                    $mediaPath = $slide['image_path'] ?? 'assets/images/12.jpeg';
                    $ext = strtolower(pathinfo($mediaPath, PATHINFO_EXTENSION));
                    $is_video = in_array($ext, ['mp4', 'webm', 'ogg', 'mov', 'avi']);
                    ?>
                    
                    <?php if ($is_video): ?>
                        <video class="hero-media" autoplay loop muted playsinline webkit-playsinline>
                            <source src="<?= htmlspecialchars($mediaPath) ?>" type="video/<?= $ext === 'mov' ? 'mp4' : $ext ?>">
                        </video>
                    <?php else: ?>
                        <img class="hero-media" src="<?= htmlspecialchars($mediaPath) ?>" alt="Hero slide">
                    <?php endif; ?>

                    <!-- Slide Bottom Buttons Overlay -->
                    <div class="slide-bottom-controls">
                        <!-- Left Corner -->
                        <div class="bottom-left-controls">
                            <?php if (!empty($slide['button_link'])): ?>
                                <a href="<?= htmlspecialchars($slide['button_link']) ?>" class="hero-action-btn shop-action-btn">
                                    🛍️ Shop Now
                                </a>
                            <?php endif; ?>
                        </div>

                        <!-- Right Corner -->
                        <div class="bottom-right-controls">
                            <?php if (!empty($slide['phone_number'])): ?>
                                <a href="tel:<?= htmlspecialchars(preg_replace('/[^0-9+]/', '', $slide['phone_number'])) ?>" class="hero-action-btn contact-action-btn">
                                    📞 Call
                                </a>
                            <?php endif; ?>

                            <?php if (!empty($slide['email_address'])): ?>
                                <a href="mailto:<?= htmlspecialchars($slide['email_address']) ?>" class="hero-action-btn email-action-btn">
                                    ✉️ Email
                                </a>
                            <?php endif; ?>

                            <?php if ($is_video): ?>
                                <button class="hero-action-btn audio-action-btn" onclick="toggleHeroAudio(event, this)">
                                    🔇 Mute
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- DOTS -->
        <div class="dots">
            <?php foreach ($hero_slides as $index => $slide): ?>
                <span class="dot <?= $index === 0 ? 'active' : '' ?>" onclick="goToSlide(<?= $index ?>)"></span>
            <?php endforeach; ?>
        </div>

    </div>

</section>

<!-- FEATURES MARQUEE SECTION -->
<div class="features-marquee">
    <div class="marquee-content">
        <div class="marquee-item">🚚 Free Delivery Over ₹500</div>
        <div class="marquee-item">🛡️ Secure Payments</div>
        <div class="marquee-item">↩️ 30-Day Returns</div>
        <div class="marquee-item">🐾 Premium Pet Care</div>
        <div class="marquee-item">⭐ 5-Star Rated Service</div>
        <!-- Duplicated for seamless loop -->
        <div class="marquee-item">🚚 Free Delivery Over ₹500</div>
        <div class="marquee-item">🛡️ Secure Payments</div>
        <div class="marquee-item">↩️ 30-Day Returns</div>
        <div class="marquee-item">🐾 Premium Pet Care</div>
        <div class="marquee-item">⭐ 5-Star Rated Service</div>
    </div>
</div>

<style>
    .category-card {
        background: transparent !important;
        border: none !important;
        box-shadow: none !important;
        padding: 10px 0 !important;
        transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1) !important;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        width: 150px;
    }
    .category-card:hover {
        background: transparent !important;
        box-shadow: none !important;
        transform: translateY(-5px) !important;
    }
    .cat-svg-circle {
        width: 90px;
        height: 90px;
        border-radius: 0 !important;
        background: transparent !important;
        border: none !important;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 15px auto;
        transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
        box-shadow: none !important;
    }
    .cat-svg-circle svg {
        opacity: 0.6;
        filter: brightness(0.9);
        transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
    }
    .category-card:hover .cat-svg-circle svg {
        opacity: 1;
        transform: scale(1.1);
        filter: brightness(1.2) drop-shadow(0 2px 8px rgba(214, 168, 108, 0.35));
    }
</style>
<!-- CATEGORIES SECTION -->
<section class="category-section fade-in">
    <div class="category-header" style="text-align: center; margin-bottom: 40px;">
        <h2 style="font-size: 36px; font-weight: 600; color: #1d1d1f;">Shop by Category</h2>
    </div>
    <div class="category-grid">
        <?php foreach ($home_categories as $cat): ?>
            <div class="category-card" onclick="window.location='shop.php?category=<?= urlencode($cat['name']) ?>'">
                <div class="cat-svg-circle">
                    <?= render_category_icon($cat, 75) ?>
                </div>
                <div class="cat-name"><?= htmlspecialchars($cat['name']) ?></div>
            </div>
        <?php endforeach; ?>
    </div>
</section>

<!-- DEAL OF THE DAY -->
<?php if ($deal_of_the_day): ?>
    <section class="deal-section fade-in">
        <div class="deal-container">
            <div class="deal-image-container">
                <img src="<?= htmlspecialchars($deal_of_the_day['main_image'] ?? 'assets/images/placeholder.jpg') ?>"
                    alt="<?= htmlspecialchars($deal_of_the_day['title']) ?>">
                <div class="deal-badge-hot">SALE</div>
            </div>
            <div class="deal-content">
                <h3 class="deal-subtitle">DEAL OF THE DAY</h3>
                <h2 class="deal-title"><?= htmlspecialchars($deal_of_the_day['title']) ?></h2>
                <div class="deal-price">
                    <span class="old-price">₹<?= number_format((float) $deal_of_the_day['old_price'], 2) ?></span>
                    <span class="new-price">₹<?= number_format((float) $deal_of_the_day['price'], 2) ?></span>
                </div>

                <div class="deal-timer" data-endtime="<?= htmlspecialchars($deal_of_the_day['end_time']) ?>">
                    <div class="time-box"><span class="days">00</span><small>Days</small></div>
                    <div class="time-box"><span class="hours">00</span><small>Hrs</small></div>
                    <div class="time-box"><span class="minutes">00</span><small>Mins</small></div>
                    <div class="time-box"><span class="seconds">00</span><small>Secs</small></div>
                </div>

                <button class="deal-btn-pro" onclick="window.location='product.php?id=<?= $deal_of_the_day['id'] ?>'">Shop
                    Deal Now</button>
            </div>
        </div>
    </section>
<?php endif; ?>

<!-- PRODUCT SECTION -->

<section class="product-section">
    <div class="product-header">
        <h2>Premium Pet Kennels</h2>
        <div class="product-filter">
            <button class="active" onclick="filterHomeProducts('all', this)">All</button>
            <?php foreach ($home_categories as $cat): ?>
                <button onclick="filterHomeProducts('<?= addslashes(strtolower($cat['name'])) ?>', this)">
                    <?= htmlspecialchars($cat['name']) ?>
                </button>
            <?php endforeach; ?>
        </div>
        <a href="shop.php" class="shop-now-btn">Shop Now →</a> <!-- Link to main shop page -->
    </div>

    <div class="product-grid">
        <?php foreach ($home_products as $p):
            $in_wishlist = in_array($p['id'], $wishlist_items);
            ?>
            <div class="product-card" data-category="<?= htmlspecialchars($p['category']) ?>"
                onclick="window.location='product.php?id=<?= $p['id'] ?>'">
                <!-- WISHLIST HEART -->
                <div class="wishlist-btn <?= $in_wishlist ? 'active' : '' ?>"
                    onclick="toggleWishlist(event, <?= $p['id'] ?>, this)">
                    <?= $in_wishlist ? '❤️' : '🤍' ?>
                </div>

                <div class="product-image">
                    <img src="<?= htmlspecialchars($p['main_image'] ?? 'assets/images/16.jpeg') ?>"
                        alt="<?= htmlspecialchars($p['title']) ?>">
                    <?php if ($p['badge']): ?>
                        <span class="badge <?= $p['badge'] ?>"><?= ucfirst($p['badge']) ?></span>
                    <?php endif; ?>
                </div>
                <div class="product-info">
                    <h4><?= htmlspecialchars($p['title']) ?></h4>
                    <div class="rating">
                        <?= str_repeat('★', $p['rating']) . str_repeat('☆', 5 - $p['rating']) ?>
                    </div>
                    <p class="price">
                        <?php if ($p['old_price']): ?>
                            <span class="old-price">₹<?= number_format($p['old_price'], 2) ?></span>
                        <?php endif; ?>
                        ₹<?= number_format($p['price'], 2) ?>
                    </p>
                    <button class="add-to-cart" <?= $p['stock_status'] == 'Sold Out' ? 'disabled' : '' ?>
                        onclick="event.stopPropagation(); addToCart(<?= $p['id'] ?>)">
                        <?= $p['stock_status'] == 'Sold Out' ? 'Sold Out' : 'Add to Cart' ?>
                    </button>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</section>
<!-- TRENDING PRODUCTS -->
<?php if (!empty($trending_products)): ?>
    <section class="product-section fade-in" style="background: transparent; box-shadow: none; padding-top:0;">
        <div class="product-header">
            <h2>Trending Best Sellers</h2>
            <a href="shop.php" class="shop-now-btn" style="background:#f5f5f7; color:#1d1d1f; border:none;">View All →</a>
        </div>

        <div class="product-grid p-grid-4">
            <?php foreach ($trending_products as $p):
                $in_wishlist = in_array($p['id'], $wishlist_items);
                ?>
                <div class="product-card" data-category="<?= htmlspecialchars($p['category']) ?>"
                    onclick="window.location='product.php?id=<?= $p['id'] ?>'">
                    <div class="wishlist-btn <?= $in_wishlist ? 'active' : '' ?>"
                        onclick="toggleWishlist(event, <?= $p['id'] ?>, this)">
                        <?= $in_wishlist ? '❤️' : '🤍' ?>
                    </div>

                    <div class="product-image">
                        <img src="<?= htmlspecialchars($p['main_image'] ?? 'assets/images/16.jpeg') ?>"
                            alt="<?= htmlspecialchars($p['title']) ?>">
                        <?php if ($p['badge']): ?>
                            <span class="badge <?= $p['badge'] ?>"><?= ucfirst($p['badge']) ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="product-info">
                        <h4><?= htmlspecialchars($p['title']) ?></h4>
                        <div class="rating">
                            <?= str_repeat('★', $p['rating']) . str_repeat('☆', 5 - $p['rating']) ?>
                        </div>
                        <p class="price">
                            <?php if ($p['old_price']): ?>
                                <span class="old-price">₹<?= number_format((float) $p['old_price'], 2) ?></span>
                            <?php endif; ?>
                            ₹<?= number_format((float) $p['price'], 2) ?>
                        </p>
                        <button class="add-to-cart" <?= $p['stock_status'] == 'Sold Out' ? 'disabled' : '' ?>
                            onclick="event.stopPropagation(); addToCart(<?= $p['id'] ?>)">
                            <?= $p['stock_status'] == 'Sold Out' ? 'Sold Out' : 'Add to Cart' ?>
                        </button>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </section>
<?php endif; ?>

<!-- PROMOTIONAL BANNER -->
<section class="promo-section fade-in">
    <div class="promo-content">
        <p class="promo-sub">UPTO 40% OFF</p>
        <h2 class="promo-title">Clearance Sale !!!</h2>
        <a href="shop.php" class="promo-btn">SHOP NOW →</a>
    </div>

    <div class="promo-image-box">
        <img src="assets/images/16.jpeg" alt="Dog Kennel">
    </div>
</section>

<!-- ARCHIPAWS IMPACT / STATS SECTION -->
<section class="stats-section fade-in">
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-number">15,000+</div>
            <div class="stat-text">Happy Pets Served</div>
        </div>
        <div class="stat-card">
            <div class="stat-number">99%</div>
            <div class="stat-text">Delivery Success Rate</div>
        </div>
        <div class="stat-card">
            <div class="stat-number">50+</div>
            <div class="stat-text">Premium Global Brands</div>
        </div>
        <div class="stat-card">
            <div class="stat-number">24/7</div>
            <div class="stat-text">Expert Pet Care Support</div>
        </div>
    </div>
</section>

<!-- TESTIMONIALS SECTION -->
<?php if (!empty($home_testimonials)): ?>
    <section class="testimonial-section fade-in">
        <div style="text-align:center; margin-bottom:50px;">
            <h2 style="font-size:36px; font-weight:600; color:#1d1d1f;">Happy Pets, Happy Owners</h2>
            <p style="color:#666; margin-top:10px;">Don't just take our word for it.</p>
        </div>
        <div class="testimonial-grid">
            <?php foreach ($home_testimonials as $t): ?>
                <div class="testimonial-card">
                    <div class="test-rating" style="color:#f5b301; font-size:20px; margin-bottom:15px;">
                        <?= str_repeat('★', $t['rating']) ?>
                    </div>
                    <p class="test-quote"
                        style="font-size:16px; color:#444; line-height:1.6; font-style:italic; margin-bottom:20px;">
                        "<?= htmlspecialchars($t['quote']) ?>"</p>
                    <div class="test-user" style="display:flex; align-items:center; gap:15px;">
                        <img src="<?= htmlspecialchars($t['image_path'] ?? 'assets/images/user-placeholder.jpg') ?>" alt="User"
                            style="width:50px; height:50px; border-radius:50%; object-fit:cover;">
                        <div class="test-info">
                            <h4 style="font-size:16px; font-weight:600; color:#1d1d1f; margin:0;">
                                <?= htmlspecialchars($t['customer_name']) ?>
                            </h4>
                            <span style="font-size:13px; color:#888;">Verified Buyer</span>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </section>
<?php endif; ?>

<!-- KENNEL FIT FINDER SECTION -->
<section class="kennel-finder-section fade-in">
    <div class="finder-container">
        <div class="finder-header">
            <span class="guide-tag">Interactive Fit Guide</span>
            <h2 class="finder-title">Find the Perfect Kennel for Your Pet</h2>
            <p class="finder-subtitle">Select your dog's size tier to discover the ideal dimensions and recommended configurations.</p>
        </div>

        <div class="finder-tabs">
            <button class="tab-btn active" onclick="selectSizeTier('small', this)">
                <span class="icon">🐶</span>
                <span class="name">Toy & Small</span>
                <span class="weight">Up to 10 kg</span>
            </button>
            <button class="tab-btn" onclick="selectSizeTier('medium', this)">
                <span class="icon">🐕</span>
                <span class="name">Medium Breed</span>
                <span class="weight">11 - 25 kg</span>
            </button>
            <button class="tab-btn" onclick="selectSizeTier('large', this)">
                <span class="icon">🐩</span>
                <span class="name">Large Breed</span>
                <span class="weight">26 - 40 kg</span>
            </button>
            <button class="tab-btn" onclick="selectSizeTier('giant', this)">
                <span class="icon">🐾</span>
                <span class="name">Giant / XL</span>
                <span class="weight">41+ kg</span>
            </button>
        </div>

        <div class="finder-content-card">
            <!-- Small Size Info -->
            <div id="size-small" class="size-detail-pane active">
                <div class="pane-grid">
                    <div class="pane-text">
                        <h3>Toy & Small Breeds</h3>
                        <p class="breed-examples"><strong>Examples:</strong> Pomeranian, Pug, French Bulldog, Shih Tzu, Chihuahua</p>
                        
                        <div class="spec-list">
                            <div class="spec-item">
                                <span class="spec-label">Recommended Size:</span>
                                <span class="spec-val">Small (S) Kennel</span>
                            </div>
                            <div class="spec-item">
                                <span class="spec-label">Dimensions (W x L x H):</span>
                                <span class="spec-val">24" x 20" x 22" (60 x 50 x 55 cm)</span>
                            </div>
                            <div class="spec-item">
                                <span class="spec-label">Key Features Needed:</span>
                                <span class="spec-val">Thermal insulation draft protection, cozy nesting design</span>
                            </div>
                        </div>

                        <p class="size-description">Smaller dogs lose body heat faster than larger ones. Our small-tier custom insulated timber dog houses provide cozy, thermal-efficient nests that protect delicate breeds from drafty conditions and ground dampness.</p>
                        
                        <a href="shop.php?category=Dogs" class="shop-size-btn">Shop Small Tier Kennels & Supplies →</a>
                    </div>
                    <div class="pane-illustration">
                        <div class="dog-avatar small-dog">
                            <span class="dog-emoji">🐶</span>
                            <div class="avatar-glow"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Medium Size Info -->
            <div id="size-medium" class="size-detail-pane">
                <div class="pane-grid">
                    <div class="pane-text">
                        <h3>Medium Breeds</h3>
                        <p class="breed-examples"><strong>Examples:</strong> Beagle, Cocker Spaniel, Border Collie, Bulldog, Shiba Inu</p>
                        
                        <div class="spec-list">
                            <div class="spec-item">
                                <span class="spec-label">Recommended Size:</span>
                                <span class="spec-val">Medium (M) Kennel</span>
                            </div>
                            <div class="spec-item">
                                <span class="spec-label">Dimensions (W x L x H):</span>
                                <span class="spec-val">32" x 26" x 28" (80 x 65 x 70 cm)</span>
                            </div>
                            <div class="spec-item">
                                <span class="spec-label">Key Features Needed:</span>
                                <span class="spec-val">Chew-proof edges, ventilation window, dual entry locks</span>
                            </div>
                        </div>

                        <p class="size-description">Active medium breeds require a secure space with ample air circulation. Our medium-tier wooden kennels feature side louvers and high-ground clearance to encourage continuous fresh airflow while preventing dampness.</p>
                        
                        <a href="shop.php?category=Dogs" class="shop-size-btn">Shop Medium Tier Kennels & Supplies →</a>
                    </div>
                    <div class="pane-illustration">
                        <div class="dog-avatar medium-dog">
                            <span class="dog-emoji">🐕</span>
                            <div class="avatar-glow"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Large Size Info -->
            <div id="size-large" class="size-detail-pane">
                <div class="pane-grid">
                    <div class="pane-text">
                        <h3>Large Breeds</h3>
                        <p class="breed-examples"><strong>Examples:</strong> Golden Retriever, German Shepherd, Labrador, Boxer, Siberian Husky</p>
                        
                        <div class="spec-list">
                            <div class="spec-item">
                                <span class="spec-label">Recommended Size:</span>
                                <span class="spec-val">Large (L) Kennel</span>
                            </div>
                            <div class="spec-item">
                                <span class="spec-label">Dimensions (W x L x H):</span>
                                <span class="spec-val">42" x 32" x 34" (105 x 80 x 85 cm)</span>
                            </div>
                            <div class="spec-item">
                                <span class="spec-label">Key Features Needed:</span>
                                <span class="spec-val">Double reinforced metal frame, orthopedic thick base support</span>
                            </div>
                        </div>

                        <p class="size-description">Large breeds exert significant weight on structural joints and beds. Our large-tier architectural pet kennels utilize premium solid hardwoods, steel bolt locking mechanisms, and extra structural bracing to support heavy pets safely.</p>
                        
                        <a href="shop.php?category=Dogs" class="shop-size-btn">Shop Large Tier Kennels & Supplies →</a>
                    </div>
                    <div class="pane-illustration">
                        <div class="dog-avatar large-dog">
                            <span class="dog-emoji">🐩</span>
                            <div class="avatar-glow"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Giant Size Info -->
            <div id="size-giant" class="size-detail-pane">
                <div class="pane-grid">
                    <div class="pane-text">
                        <h3>Giant & Extra-Large Breeds</h3>
                        <p class="breed-examples"><strong>Examples:</strong> Great Dane, Rottweiler, Saint Bernard, Mastiff, Irish Wolfhound</p>
                        
                        <div class="spec-list">
                            <div class="spec-item">
                                <span class="spec-label">Recommended Size:</span>
                                <span class="spec-val">Extra Large (XL) Kennel</span>
                            </div>
                            <div class="spec-item">
                                <span class="spec-label">Dimensions (W x L x H):</span>
                                <span class="spec-val">52" x 38" x 40" (130 x 95 x 100 cm)</span>
                            </div>
                            <div class="spec-item">
                                <span class="spec-label">Key Features Needed:</span>
                                <span class="spec-val">Extra high doors, heavy duty hinges, orthopedic support foam compatibility</span>
                            </div>
                        </div>

                        <p class="size-description">Giant breed dogs have delicate joints and require maximum headroom. Our XL custom pet homes feature tall entry arches and heavy-gauge hardware, with plenty of space to slide in a premium orthopedic dog mattress.</p>
                        
                        <a href="shop.php?category=Dogs" class="shop-size-btn">Shop XL Tier Kennels & Supplies →</a>
                    </div>
                    <div class="pane-illustration">
                        <div class="dog-avatar giant-dog">
                            <span class="dog-emoji">🐾</span>
                            <div class="avatar-glow"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<?php
include 'footer.php';
?>



<script>
    function toggleHeroAudio(e, btn) {
        if (e) e.stopPropagation();
        const activeSlide = btn.closest('.slide');
        const video = activeSlide.querySelector('video');
        if (video) {
            video.muted = !video.muted;
            if (video.muted) {
                btn.innerHTML = '🔇 Mute';
                btn.classList.remove('sound-on');
            } else {
                btn.innerHTML = '🔊 Sound On';
                btn.classList.add('sound-on');
            }
        }
    }

    /* ===== FADE-IN (GLOBAL) ===== */
    const faders = document.querySelectorAll('.fade-in');

    const observer = new IntersectionObserver(entries => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('show');
            }
        });
    });

    faders.forEach(el => observer.observe(el));


    /* ===== PROMO SECTION EFFECTS (SAFE) ===== */
    const promoSection = document.querySelector(".promo-section");
    const imgBox = document.querySelector(".promo-image-box");

    if (promoSection && imgBox) {

        /* PARALLAX */
        window.addEventListener("scroll", () => {
            const rect = promoSection.getBoundingClientRect();

            if (rect.top < window.innerHeight && rect.bottom > 0) {
                let offset = rect.top * -0.05;
                imgBox.style.setProperty('--parallax', offset + 'px');
            }
        });

        /* CURSOR LIGHT */
        promoSection.addEventListener("mousemove", (e) => {
            const rect = promoSection.getBoundingClientRect();

            promoSection.style.setProperty("--x", (e.clientX - rect.left) + "px");
            promoSection.style.setProperty("--y", (e.clientY - rect.top) + "px");
        });
    }

    /* ===== DEAL TIMER ===== */
    const dealTimer = document.querySelector('.deal-timer');
    if (dealTimer) {
        const endTime = new Date(dealTimer.dataset.endtime).getTime();

        const updateTimer = setInterval(() => {
            const now = new Date().getTime();
            const distance = endTime - now;

            if (distance < 0) {
                clearInterval(updateTimer);
                dealTimer.innerHTML = "<div style='font-weight:600; color:#ff3b30;'>Deal Expired</div>";
                return;
            }

            const days = Math.floor(distance / (1000 * 60 * 60 * 24));
            const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
            const seconds = Math.floor((distance % (1000 * 60)) / 1000);

            dealTimer.querySelector('.days').innerText = String(days).padStart(2, '0');
            dealTimer.querySelector('.hours').innerText = String(hours).padStart(2, '0');
            dealTimer.querySelector('.minutes').innerText = String(minutes).padStart(2, '0');
            dealTimer.querySelector('.seconds').innerText = String(seconds).padStart(2, '0');
        }, 1000);
    }

    function filterHomeProducts(category, btn) {
        // Toggle active button class
        const filterBtns = document.querySelectorAll('.product-filter button');
        filterBtns.forEach(b => b.classList.remove('active'));
        btn.classList.add('active');

        // Filter cards
        const cards = document.querySelectorAll('.product-grid .product-card');
        cards.forEach(card => {
            const cardCat = (card.dataset.category || '').toLowerCase();
            if (category === 'all' || cardCat.includes(category)) {
                card.style.display = 'flex';
                // Add fade-in transition
                card.style.opacity = '0';
                setTimeout(() => {
                    card.style.opacity = '1';
                }, 50);
            } else {
                card.style.display = 'none';
            }
        });
    }
</script>
<script>
    function selectSizeTier(tier, btn) {
        // Remove active class from all buttons
        const tabBtns = document.querySelectorAll('.finder-tabs .tab-btn');
        tabBtns.forEach(b => b.classList.remove('active'));
        
        // Add active class to clicked button
        btn.classList.add('active');
        
        // Hide all detail panes
        const panes = document.querySelectorAll('.size-detail-pane');
        panes.forEach(p => p.classList.remove('active'));
        
        // Show selected pane
        const targetPane = document.getElementById(`size-${tier}`);
        if (targetPane) {
            targetPane.classList.add('active');
        }
    }
</script>


</body>

</html>