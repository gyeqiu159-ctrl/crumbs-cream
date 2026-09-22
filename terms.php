<?php
$site = [
    'brand'       => 'Crumb & Cream',
    'year'        => date('Y'),
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Terms and Conditions | Crumb & Cream</title>
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,600;0,700;1,600&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <!-- Styles -->
    <link rel="stylesheet" href="css/style.css">
    <link rel="icon" href="images/image-removebg-preview (1).svg" type="image/svg+xml">
    <style>
        .page-content { padding: 140px 0 80px; min-height: 70vh; background: var(--cream); }
        .terms-box { max-width: 860px; margin: 0 auto; background: var(--cream-white); padding: 50px 60px; border-radius: var(--radius-lg); box-shadow: var(--shadow-card); }
        .terms-box h1 { margin-bottom: 24px; font-family: var(--font-display); color: var(--cocoa); font-size: 2.8rem; }
        .terms-box h2 { margin-top: 40px; margin-bottom: 16px; font-size: 1.5rem; font-family: var(--font-display); color: var(--cocoa); }
        .terms-box p, .terms-box li { margin-bottom: 16px; line-height: 1.7; color: var(--cocoa-soft); font-family: var(--font-body); font-size: 1.05rem; }
        .terms-box ul { margin-left: 24px; margin-bottom: 24px; }
        
        .alert-box {
            background: var(--cream-deep);
            padding: 20px 24px;
            border-left: 4px solid var(--caramel);
            margin: 30px 0;
            border-radius: var(--radius-sm);
        }
        .alert-box strong { color: var(--cocoa); display: block; margin-bottom: 8px; font-size: 1.1rem; }
        
        @media (max-width: 768px) {
            .terms-box { padding: 40px 30px; }
            .terms-box h1 { font-size: 2.2rem; }
        }
    </style>
</head>
<body>
    <!-- ================= NAVIGATION ================= -->
    <header class="navbar" id="navbar">
        <div class="container nav-inner">
            <a href="index.php" class="logo">
                <span class="logo-mark" aria-hidden="true"></span>
                <?php echo htmlspecialchars($site['brand']); ?>
            </a>

            <nav>
                <ul class="nav-links" id="navLinks">
                    <li><a href="index.php#home" class="nav-link">Home</a></li>
                    <li><a href="index.php#about" class="nav-link">About</a></li>
                    <li><a href="index.php#product" class="nav-link">Product</a></li>
                    <li><a href="index.php#why-us" class="nav-link">Why Us</a></li>
                    <li><a href="index.php#reviews" class="nav-link">Reviews</a></li>
                    <li><a href="index.php#faq" class="nav-link">FAQ</a></li>
                    <li><a href="index.php#contact" class="nav-link">Contact</a></li>
                </ul>
            </nav>

            <div class="nav-cta">
                <a href="index.php#contact" class="btn btn-primary">Order Now</a>
                <a href="admin/login.php" class="btn btn-outline">Log In</a>
                <button class="hamburger" id="hamburger" aria-label="Toggle navigation menu" aria-expanded="false">
                    <span></span><span></span><span></span>
                </button>
            </div>
        </div>
    </header>

    <main class="page-content container">
        <div class="terms-box">
            <h1>Terms and Conditions</h1>
            <p><strong>Last Updated: <?php echo date('F j, Y'); ?></strong></p>

            <div class="alert-box">
                <strong>Disclaimer for Educational Purposes:</strong>
                This website is a student project created for educational purposes, but it <strong>operates as a real student-run store.</strong> Real transactions can be made and actual products will be provided in accordance with the project's parameters.
            </div>

            <h2>1. Acceptance of Terms</h2>
            <p>By accessing or using this website to browse or place orders, you agree to be bound by these Terms and Conditions. If you do not agree with any part of these terms, please do not use the website.</p>

            <h2>2. Orders and Purchases</h2>
            <p>While this is an educational project, orders submitted and payments made are for real physical products fulfilled by the students running this store. By submitting an order inquiry and proceeding with a payment, you agree to complete the purchase.</p>

            <h2>3. Privacy and Data</h2>
            <p>Any data submitted via our contact and order forms (such as names, emails, or phone numbers) is used to process your orders and coordinate delivery or pickup. We handle your data responsibly within the scope of our project operations.</p>

            <h2>4. Cancellations and Refunds</h2>
            <p>Because products are freshly made to order, cancellations or modifications must be made promptly before your order enters production. Please contact us directly for any issues regarding your order.</p>

            <h2>5. Limitation of Liability</h2>
            <p>The creators of this website shall not be held liable for any damages or misunderstandings arising from the use of this site. This is a student-led initiative; we strive for quality and excellent service, but operate under the context of an educational project.</p>
        </div>
    </main>

    <!-- ================= FOOTER ================= -->
    <footer class="footer">
        <div class="container footer-top">
            <div>
                <a href="index.php" class="logo">
                    <span class="logo-mark" aria-hidden="true"></span>
                    <?php echo htmlspecialchars($site['brand']); ?>
                </a>
                <p class="footer-tagline">Sweet moments, one bite at a time.</p>
            </div>

            <div>
                <div class="footer-heading">Explore</div>
                <ul class="footer-links">
                    <li><a href="index.php#home">Home</a></li>
                    <li><a href="index.php#product">Product</a></li>
                    <li><a href="index.php#reviews">Reviews</a></li>
                    <li><a href="index.php#faq">FAQ</a></li>
                    <li><a href="index.php#contact">Contact</a></li>
                    <li><a href="terms.php">Terms & Conditions</a></li>
                </ul>
            </div>

            <div>
                <div class="footer-heading">Follow Us</div>
                <div class="footer-social">
                    <a href="https://www.facebook.com/bsiscrumbandcream" aria-label="Facebook"><i class="fa-brands fa-facebook-f"></i></a>
                    <a href="https://www.instagram.com/bsiscrumbandcream" aria-label="Instagram"><i class="fa-brands fa-instagram"></i></a>
                    <a href="https://m.me/bsiscrumbandcream" aria-label="Messenger"><i class="fa-brands fa-facebook-messenger"></i></a>
                </div>
            </div>
        </div>

        <div class="container footer-bottom">
            <p>&copy; <?php echo htmlspecialchars($site['year']); ?> <?php echo htmlspecialchars($site['brand']); ?>. All Rights Reserved.</p>
            <p style="margin-top: 10px; color: rgba(251, 243, 228, 0.9);">Disclaimer: This website is a student project created for educational purposes, operating as a real student-run store.</p>
        </div>
    </footer>

    <script src="js/script.js"></script>
</body>
</html>
