<?php include 'header.php'; ?>

<style>
    /* Apple-Level Pro overrides for about.php */
    body {
        background: #fbfbfd;
        padding-top: 64px;
    }

    .pro-about-hero {
        text-align: center;
        padding: 140px 20px 80px;
    }

    .pro-about-hero h1 {
        font-size: 80px;
        font-weight: 700;
        color: #1d1d1f;
        letter-spacing: -0.04em;
        line-height: 1.05;
        margin-bottom: 20px;
    }

    .pro-about-hero p {
        font-size: 24px;
        color: #86868b;
        letter-spacing: -0.01em;
        max-width: 600px;
        margin: 0 auto;
        font-weight: 500;
    }

    .pro-hero-bleed {
        width: 100%;
        max-width: 1400px;
        margin: 0 auto;
        border-radius: 40px;
        overflow: hidden;
        box-shadow: 0 40px 100px rgba(0, 0, 0, 0.1);
        height: 600px;
        position: relative;
    }

    .pro-hero-bleed img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .pro-story-split {
        max-width: 1200px;
        margin: 120px auto;
        padding: 0 40px;
        display: flex;
        gap: 80px;
        align-items: center;
    }

    .pro-story-text {
        flex: 1;
    }

    .pro-story-text h2 {
        font-size: 48px;
        font-weight: 700;
        color: #1d1d1f;
        letter-spacing: -0.03em;
        margin-bottom: 30px;
    }

    .pro-story-text p {
        font-size: 21px;
        color: #86868b;
        line-height: 1.6;
        letter-spacing: -0.01em;
        margin-bottom: 20px;
    }

    .pro-story-img {
        flex: 1;
        border-radius: 32px;
        overflow: hidden;
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.06);
        height: 500px;
    }

    .pro-story-img img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .pro-values {
        max-width: 1200px;
        margin: 0 auto 120px;
        padding: 0 40px;
        text-align: center;
    }

    .pro-values h2 {
        font-size: 48px;
        font-weight: 700;
        color: #1d1d1f;
        letter-spacing: -0.03em;
        margin-bottom: 60px;
    }

    .pro-values-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 40px;
    }

    .pro-value-card {
        background: #fff;
        border-radius: 32px;
        padding: 50px 40px;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.03);
        transition: transform 0.4s cubic-bezier(0.16, 1, 0.3, 1);
    }

    .pro-value-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.06);
    }

    .pro-value-icon {
        font-size: 48px;
        margin-bottom: 20px;
    }

    .pro-value-card h4 {
        font-size: 21px;
        font-weight: 600;
        color: #1d1d1f;
        margin-bottom: 10px;
    }

    .pro-value-card p {
        font-size: 17px;
        color: #86868b;
        line-height: 1.5;
    }

    @media(max-width: 992px) {
        .pro-about-hero h1 {
            font-size: 56px;
        }

        .pro-story-split {
            flex-direction: column;
        }

        .pro-values-grid {
            grid-template-columns: 1fr;
        }

        .pro-hero-bleed {
            border-radius: 20px;
            height: 400px;
        }
    }
</style>

<div class="pro-about-hero">
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
        <span style="color: #1d1d1f; font-weight: 600;">About Us</span>
    </div>
    <h1>Designed in NY. <br>Crafted for pets.</h1>
    <p>Discover the passion, quality, and luxury behind ARCHIPAWS's premium goods.</p>
</div>

<div class="pro-hero-bleed" style="padding: 0 20px; box-sizing: border-box;">
    <div style="width: 100%; height: 100%; border-radius: 40px; overflow:hidden;">
        <img src="assets/images/19.webp" alt="Archipaws Kennel">
    </div>
</div>

<div class="pro-story-split">
    <div class="pro-story-text">
        <h2>Our Philosophy.</h2>
        <p>At ARCHIPAWS, we believe that pets are more than just animals; they are beloved family members. That's why we
            have dedicated ourselves to providing the highest quality, most comfortable, and luxurious living spaces for
            your furry companions.</p>
        <p>We source only premium, durable, and pet-safe materials to ensure that every product not only looks stunning
            but also stands the test of time.</p>
    </div>
    <div class="pro-story-img">
        <img src="assets/images/13.jpeg" alt="Craftsmanship">
    </div>
</div>

<div class="pro-values">
    <h2>Why ARCHIPAWS?</h2>
    <div class="pro-values-grid">
        <div class="pro-value-card">
            <div class="pro-value-icon">🌟</div>
            <h4>Unmatched Quality</h4>
            <p>Every product is rigorously tested to meet high standards of durability and comfort.</p>
        </div>
        <div class="pro-value-card">
            <div class="pro-value-icon">✨</div>
            <h4>Modern Aesthetic</h4>
            <p>Designed to complement your home, our products are as stylish as they are functional.</p>
        </div>
        <div class="pro-value-card">
            <div class="pro-value-icon">💚</div>
            <h4>Pet-Safe Materials</h4>
            <p>We strictly avoid toxic chemicals, ensuring a healthy environment for your pets.</p>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>