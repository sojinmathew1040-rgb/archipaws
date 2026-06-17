<?php include 'header.php'; ?>

<style>
    /* Apple-Level Pro overrides for contact.php */
    body {
        background: #fbfbfd;
        padding-top: 64px;
    }

    .pro-contact-wrap {
        max-width: 1400px;
        margin: 80px auto;
        padding: 0 40px;
        display: flex;
        gap: 80px;
        align-items: center;
        min-height: 70vh;
    }

    .pro-contact-left {
        flex: 1;
    }

    .pro-contact-left h1 {
        font-size: 80px;
        font-weight: 700;
        color: #1d1d1f;
        letter-spacing: -0.04em;
        line-height: 1;
        margin-bottom: 30px;
    }

    .pro-contact-left p {
        font-size: 24px;
        color: #86868b;
        letter-spacing: -0.01em;
        margin-bottom: 50px;
        font-weight: 500;
    }

    .pro-contact-details {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 30px;
    }

    .pro-info-block h4 {
        font-size: 15px;
        font-weight: 600;
        color: #1d1d1f;
        margin-bottom: 8px;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }

    .pro-info-block p {
        font-size: 17px;
        color: #86868b;
        line-height: 1.5;
    }

    .pro-contact-right {
        flex: 1;
        background: #fff;
        border-radius: 40px;
        padding: 60px;
        box-shadow: 0 20px 80px rgba(0, 0, 0, 0.05);
    }

    .pro-form-group {
        margin-bottom: 25px;
    }

    .pro-form-group input,
    .pro-form-group textarea {
        width: 100%;
        padding: 18px 20px;
        border: 1px solid rgba(0, 0, 0, 0.08);
        border-radius: 16px;
        font-size: 17px;
        color: #1d1d1f;
        background: #fcfcfd;
        transition: 0.3s;
        box-sizing: border-box;
    }

    .pro-form-group input:focus,
    .pro-form-group textarea:focus {
        outline: none;
        border-color: #007aff;
        box-shadow: 0 0 0 4px rgba(0, 122, 255, 0.1);
        background: #fff;
    }

    .pro-form-btn {
        width: 100%;
        padding: 18px;
        background: #1d1d1f;
        color: #fff;
        border: none;
        border-radius: 980px;
        font-size: 17px;
        font-weight: 600;
        cursor: pointer;
        transition: transform 0.3s cubic-bezier(0.16, 1, 0.3, 1), background 0.3s;
    }

    .pro-form-btn:hover {
        background: #333336;
        transform: scale(0.98);
    }

    @media(max-width: 992px) {
        .pro-contact-wrap {
            flex-direction: column;
            padding: 0 20px;
            gap: 40px;
            margin-top: 40px;
        }

        .pro-contact-left h1 {
            font-size: 56px;
        }

        .pro-contact-right {
            padding: 40px 30px;
            width: 100%;
            box-sizing: border-box;
        }

        .pro-contact-details {
            grid-template-columns: 1fr;
        }
    }
</style>

<div class="pro-contact-wrap">

    <div class="pro-contact-left">
        <div
            style="display: inline-flex; align-items: center; gap: 12px; background: rgba(0,0,0,0.03); padding: 8px 20px; border-radius: 100px; font-size: 13px; font-weight: 500; letter-spacing: 0.02em; margin-bottom: 24px; border: 1px solid rgba(0,0,0,0.05); user-select: none;">
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
            <span style="color: #1d1d1f; font-weight: 600;">Contact Us</span>
        </div>
        <h1>Let's talk.</h1>
        <p>We'd love to hear from you. Reach out to our dedicated support team today.</p>

        <div class="pro-contact-details">
            <div class="pro-info-block">
                <h4>📍 Location</h4>
                <p>123 Pet Street, Animal Ave<br>New York, NY 10001, USA</p>
            </div>
            <div class="pro-info-block">
                <h4>📞 Phone</h4>
                <p>+980 34 984089<br>+1 800 PET LOVE</p>
            </div>
            <div class="pro-info-block">
                <h4>✉️ Email</h4>
                <p>support@archipaws.com<br>hello@archipaws.com</p>
            </div>
            <div class="pro-info-block">
                <h4>🕒 Hours</h4>
                <p>Mon-Fri: 9AM - 8PM<br>Sat-Sun: 10AM - 6PM</p>
            </div>
        </div>
    </div>

    <div class="pro-contact-right">
        <form action="#" method="POST" onsubmit="event.preventDefault(); alert('Message sent securely.');">
            <div class="pro-form-group">
                <input type="text" placeholder="Your Name" required>
            </div>
            <div class="pro-form-group">
                <input type="email" placeholder="Email Address" required>
            </div>
            <div class="pro-form-group">
                <input type="text" placeholder="Subject">
            </div>
            <div class="pro-form-group">
                <textarea rows="5" placeholder="How can we help?" required></textarea>
            </div>
            <button type="submit" class="pro-form-btn">Send Message</button>
        </form>
    </div>

</div>

<?php include 'footer.php'; ?>