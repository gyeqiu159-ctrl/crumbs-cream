<?php

$site = [
    'brand'       => 'Crumb & Cream',
    'tagline'     => 'Sweet moments, one bite at a time.',
    'year'        => date('Y'),
];

$product = [
    'name'        => 'Graham Bars',
    'price_from'  => '30.00',
    'sizes'       => [
        ['label' => '1 Pieces',  'price' => '30.00'],
        ['label' => '2 Pieces',  'price' => '60.00'],
        ['label' => '4 Pieces', 'price' => '120.00'],
    ],
    'flavors'     => [
        ['label' => 'Mango', 'description' => 'A tropical burst of flavor'],
        ['label' => 'Milo Flavor', 'description' => 'Rich chocolate malt taste'],
        ['label' => 'Cookies and Cream', 'description' => 'Classic creamy cookies delight'],
    ],
];

$contact = [
    'facebook'  => 'facebook.com/bsiscrumbandcream',
    'messenger' => 'm.me/bsiscrumbandcream',
    'phone'     => '+63 991 508 5874',
    'email'     => 'bachelorofsis@gmail.com',
    'location'  => 'Blk 6 HillCrest Village, Caloocan City, Metro Manila',
];

// ---- Order Inquiry form handling (optional MySQL storage via Laragon) ----
// The rest of the page works fine even if the database isn't set up yet;
// only this form needs it. See README.md for the Laragon setup guide.
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/products.php';

$orderFeedback = ['type' => null, 'message' => '', 'order_id' => null];

// Map flavors to their image files
function get_flavor_image($flavor_label) {
    $images = [
        'Mango'           => 'images/mangoflavor.png',
        'Milo Flavor'     => 'images/miloflavor.png',
        'Cookies and Cream' => 'images/cookiesandcream.png',
    ];
    return $images[$flavor_label] ?? 'images/image-removebg-preview (1).svg';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['order_inquiry'])) {
    $name        = trim($_POST['customer_name'] ?? '');
    $contactInfo = trim($_POST['contact_info'] ?? '');
    $size        = trim($_POST['order_size'] ?? '');
    $flavor      = trim($_POST['order_flavor'] ?? '');
    $qty         = (int) ($_POST['order_qty'] ?? 1);
    $message     = trim($_POST['order_message'] ?? '');

    if ($name === '' || $contactInfo === '' || $size === '' || $flavor === '') {
        $orderFeedback = [
            'type'    => 'error',
            'message' => 'Please fill in your name, contact info, size, and flavor before submitting.',
        ];
    } else {
        $qty = max(1, min(50, $qty));
        $pdo = get_db_connection();

        if ($pdo === null) {
            $orderFeedback = [
                'type'    => 'error',
                'message' => 'We could not save your inquiry right now (database unavailable). Please message us directly instead.',
            ];
        } else {
            try {
                $unitPrice = get_size_price($size);
                $amount    = $unitPrice !== null ? $unitPrice * $qty : null;

                $stmt = $pdo->prepare(
                    'INSERT INTO orders (customer_name, contact_info, size, flavor, quantity, message, amount)
                     VALUES (:name, :contact, :size, :flavor, :qty, :message, :amount)'
                );
                $stmt->execute([
                    ':name'    => $name,
                    ':contact' => $contactInfo,
                    ':size'    => $size,
                    ':flavor'  => $flavor,
                    ':qty'     => $qty,
                    ':message' => $message !== '' ? $message : null,
                    ':amount'  => $amount,
                ]);
                $newOrderId = (int) $pdo->lastInsertId();
                $orderFeedback = [
                    'type'     => 'success',
                    'message'  => 'Thank you, ' . $name . '! We received your order inquiry and will reach out shortly.',
                    'order_id' => $newOrderId,
                ];
                // Clear submitted values after a successful save.
                $name = $contactInfo = $message = '';
                $size = $flavor = '';
                $qty = 1;
            } catch (PDOException $e) {
                error_log('Order insert failed: ' . $e->getMessage());
                $orderFeedback = [
                    'type'    => 'error',
                    'message' => 'Something went wrong saving your inquiry. Please try again or message us directly.',
                ];
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crumb & Cream | Handcrafted Graham Bars</title>
    <meta name="description" content="Crumb & Cream Graham Bars — creamy, crunchy, handcrafted layered dessert bars made fresh to order. A little sweetness in every bite.">
    <meta name="keywords" content="graham bars, graham cake, dessert, Filipino dessert, Crumb and Cream">

    <!-- Open Graph -->
    <meta property="og:title" content="Crumb & Cream | Handcrafted Graham Bars">
    <meta property="og:description" content="Creamy, crunchy, handcrafted Graham Bars made fresh to order.">
    <meta property="og:type" content="website">
    <meta property="og:image" content="images/image-removebg-preview (1).svg">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,600;0,700;1,600&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer">

    <!-- Styles -->
    <link rel="stylesheet" href="css/style.css">
    <link rel="icon" href="images/image-removebg-preview (1).svg" type="image/svg+xml">
</head>
<body>

    <!-- ================= NAVIGATION ================= -->
    <header class="navbar" id="navbar">
        <div class="container nav-inner">
            <a href="#" class="logo">
                <span class="logo-mark" aria-hidden="true"></span>
                <?php echo htmlspecialchars($site['brand']); ?>
            </a>

            <nav>
                <ul class="nav-links" id="navLinks">
                    <li><a href="#home" class="nav-link">Home</a></li>
                    <li><a href="#about" class="nav-link">About</a></li>
                    <li><a href="#product" class="nav-link">Product</a></li>
                    <li><a href="#why-us" class="nav-link">Why Us</a></li>
                    <li><a href="#reviews" class="nav-link">Reviews</a></li>
                    <li><a href="#faq" class="nav-link">FAQ</a></li>
                    <li><a href="#contact" class="nav-link">Contact</a></li>
                </ul>
            </nav>

            <div class="nav-cta">
                <a href="#contact" class="btn btn-primary">Order Now</a>
                <a href="admin/login.php" class="btn btn-outline">Log In</a>
                <button class="hamburger" id="hamburger" aria-label="Toggle navigation menu" aria-expanded="false">
                    <span></span><span></span><span></span>
                </button>
            </div>
        </div>
    </header>

    <!-- ================= HERO ================= -->
    <section class="hero" id="home">
        <div class="container hero-inner">
            <div class="hero-content">
                <div class="hero-badge">
                    <span class="dot"></span> Handcrafted with Love
                </div>
                <h1 class="hero-title">A Little <em>Sweetness</em> in Every Bite.</h1>
                <p class="hero-text">Indulge in our creamy, crunchy, and delicious Graham Bars — made to turn every snack break into a sweet moment.</p>
                <div class="hero-actions">
                    <a href="#contact" class="btn btn-primary">Order Now</a>
                    <a href="#product" class="btn btn-outline">Explore Product</a>
                </div>
            </div>

            <div class="hero-visual">
                <div class="hero-visual-frame">
                    <div class="hero-shape shape-1" aria-hidden="true"></div>
                    <div class="hero-shape shape-2" aria-hidden="true"></div>
                    <img src="images/image-removebg-preview (1).svg" alt="Layered Graham Bars with creamy filling, ready to be sliced and served" class="hero-product-img" width="400" height="400">
                    <div class="floating-card">
                        <div class="fc-icon" aria-hidden="true"><i class="fa-solid fa-leaf"></i></div>
                        <div>
                            <div class="fc-title">Freshly Made</div>
                            <div class="fc-sub">Made with care</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ================= PRODUCT HIGHLIGHT ================= -->
    <section class="highlight section-pad" id="about">
        <div class="container">
            <div class="section-head reveal">
                <span class="eyebrow">The Recipe</span>
                <h2>Made for Sweet Moments</h2>
                <p>Our Graham Bars combine delicious layers of creamy goodness and crunchy graham goodness in one satisfying treat.</p>
            </div>

            <div class="feature-grid">
                <div class="feature-card reveal">
                    <div class="feature-icon"><i class="fa-solid fa-ice-cream"></i></div>
                    <h3>Creamy</h3>
                    <p>Smooth and delicious cream in every bite.</p>
                </div>
                <div class="feature-card reveal reveal-delay-1">
                    <div class="feature-icon"><i class="fa-solid fa-cookie"></i></div>
                    <h3>Crunchy</h3>
                    <p>Perfectly layered graham crackers for that satisfying texture.</p>
                </div>
                <div class="feature-card reveal reveal-delay-2">
                    <div class="feature-icon"><i class="fa-solid fa-seedling"></i></div>
                    <h3>Fresh</h3>
                    <p>Prepared with care to give you a delicious snack every time.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- ================= FLAVORS CAROUSEL ================= -->
    <section class="flavors-carousel section-pad" id="flavors">
        <div class="container">
            <div class="section-head reveal">
                <span class="eyebrow">Our Flavors</span>
                <h2>Choose Your Favorite Flavor</h2>
                <p>Each flavor offers a unique taste experience, all with the same creamy, crunchy goodness.</p>
            </div>

            <div class="carousel-wrapper">
                <button class="carousel-nav carousel-nav-prev" id="carouselPrev" aria-label="Previous flavor">
                    <i class="fa-solid fa-chevron-left"></i>
                </button>

                <div class="carousel-container">
                    <div class="carousel-track" id="carouselTrack">
                        <?php foreach ($product['flavors'] as $index => $flavorOption): ?>
                            <div class="carousel-slide<?php echo $index === 0 ? ' active' : ''; ?>">
                                <div class="flavor-card reveal">
                                    <div class="flavor-image">
                                        <img src="<?php echo htmlspecialchars(get_flavor_image($flavorOption['label'])); ?>" alt="<?php echo htmlspecialchars($flavorOption['label']); ?> Graham Bars" width="300" height="300">
                                    </div>
                                    <div class="flavor-info">
                                        <h3><?php echo htmlspecialchars($flavorOption['label']); ?></h3>
                                        <?php if (isset($flavorOption['description'])): ?>
                                            <p><?php echo htmlspecialchars($flavorOption['description']); ?></p>
                                        <?php else: ?>
                                            <p>Experience the delightful taste of <?php echo htmlspecialchars($flavorOption['label']); ?> in every bite of our signature Graham Bars.</p>
                                        <?php endif; ?>
                                        <a href="#contact" class="btn btn-primary">Order <?php echo htmlspecialchars($flavorOption['label']); ?></a>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <button class="carousel-nav carousel-nav-next" id="carouselNext" aria-label="Next flavor">
                    <i class="fa-solid fa-chevron-right"></i>
                </button>
            </div>

            <div class="carousel-indicators">
                <?php foreach ($product['flavors'] as $index => $flavorOption): ?>
                    <button class="indicator<?php echo $index === 0 ? ' active' : ''; ?>" 
                            data-slide="<?php echo $index; ?>" 
                            aria-label="Go to <?php echo htmlspecialchars($flavorOption['label']); ?>"></button>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- ================= PRODUCT SHOWCASE ================= -->
    <section class="showcase section-pad" id="product">
        <div class="container showcase-inner">
            <div class="showcase-visual reveal">
                <img src="images/cookiesandcream.png" alt="Close-up of stacked <?php echo htmlspecialchars($product['name']); ?> showing layers of graham crust and cream" width="350" height="350">
                <div class="price-tag">
                    <span class="amount">₱<?php echo htmlspecialchars($product['price_from']); ?></span>
                    <span class="from">starts at</span>
                </div>
            </div>

            <div class="showcase-content reveal reveal-delay-1">
                <span class="eyebrow">Our Signature</span>
                <h2><?php echo htmlspecialchars($product['name']); ?></h2>
                <p>Layers of buttery graham crust hugging a smooth, creamy filling — sliced into bars and ready whenever a craving hits. Every batch is made fresh, never rushed.</p>

                <span class="size-label">Available Sizes</span>
                <div class="size-options" id="sizeOptions">
                    <?php foreach ($product['sizes'] as $i => $size): ?>
                        <button type="button" class="size-option<?php echo $i === 0 ? ' active' : ''; ?>" data-price="<?php echo htmlspecialchars($size['price']); ?>">
                            <?php echo htmlspecialchars($size['label']); ?>
                        </button>
                    <?php endforeach; ?>
                </div>

                <div class="qty-row">
                    <span class="qty-label">Quantity</span>
                    <div class="qty-selector">
                        <button type="button" class="qty-btn" id="qtyMinus" aria-label="Decrease quantity">−</button>
                        <span class="qty-value" id="qtyValue">1</span>
                        <button type="button" class="qty-btn" id="qtyPlus" aria-label="Increase quantity">+</button>
                    </div>
                </div>

                <a href="#contact" class="btn btn-primary">Order Now</a>
            </div>
        </div>
    </section>

    <!-- ================= WHY CHOOSE US ================= -->
    <section class="why-us section-pad" id="why-us">
        <div class="container">
            <div class="section-head reveal">
                <span class="eyebrow">Why Choose Us</span>
                <h2>Why You'll Love Our Graham Bars</h2>
            </div>

            <div class="why-grid">
                <div class="why-card reveal">
                    <div class="why-icon"><i class="fa-solid fa-clock"></i></div>
                    <h3>Freshly Prepared</h3>
                    <p>Made in small batches so every bar tastes its best.</p>
                </div>
                <div class="why-card reveal reveal-delay-1">
                    <div class="why-icon"><i class="fa-solid fa-heart"></i></div>
                    <h3>Delicious & Creamy</h3>
                    <p>A rich, smooth filling balanced with crunchy layers.</p>
                </div>
                <div class="why-card reveal reveal-delay-2">
                    <div class="why-icon"><i class="fa-solid fa-peso-sign"></i></div>
                    <h3>Affordable</h3>
                    <p>Treat yourself without stretching your budget.</p>
                </div>
                <div class="why-card reveal reveal-delay-3">
                    <div class="why-icon"><i class="fa-solid fa-gift"></i></div>
                    <h3>Perfect for Every Occasion</h3>
                    <p>Great for snacks, gifts, or sharing with friends.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- ================= HOW TO ORDER ================= -->
    <section class="order-steps section-pad">
        <div class="container">
            <div class="section-head reveal">
                <span class="eyebrow">Simple Process</span>
                <h2>How to Order</h2>
                <p>From craving to first bite in three easy steps.</p>
            </div>

            <div class="steps-grid">
                <div class="step-card reveal">
                    <div class="step-number">01</div>
                    <h3>Choose</h3>
                    <p>Choose your preferred quantity.</p>
                </div>
                <div class="step-card reveal reveal-delay-1">
                    <div class="step-number">02</div>
                    <h3>Order</h3>
                    <p>Send us your order through our contact channels.</p>
                </div>
                <div class="step-card reveal reveal-delay-2">
                    <div class="step-number">03</div>
                    <h3>Enjoy</h3>
                    <p>Receive your Graham Bars and enjoy every bite.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- ================= REVIEWS ================= -->
    <section class="reviews section-pad" id="reviews">
        <div class="container">
            <div class="section-head reveal">
                <span class="eyebrow">Testimonials</span>
                <h2>What Our Customers Say</h2>
            </div>

            <div class="review-grid">
                <div class="review-card reveal">
                    <div class="stars">★★★★★</div>
                    <p>"Super creamy and delicious! The graham layers give it the perfect crunch."</p>
                    <div class="reviewer">
                        <div class="reviewer-avatar">M</div>
                        <div class="reviewer-name">Maria</div>
                    </div>
                </div>
                <div class="review-card reveal reveal-delay-1">
                    <div class="stars">★★★★★</div>
                    <p>"Perfect for merienda! Will definitely order again."</p>
                    <div class="reviewer">
                        <div class="reviewer-avatar">A</div>
                        <div class="reviewer-name">Angela</div>
                    </div>
                </div>
                <div class="review-card reveal reveal-delay-2">
                    <div class="stars">★★★★★</div>
                    <p>"Simple, affordable, and really tasty!"</p>
                    <div class="reviewer">
                        <div class="reviewer-avatar">J</div>
                        <div class="reviewer-name">John</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ================= CALL TO ACTION ================= -->
    <section class="cta-section section-pad">
        <div class="container cta-inner reveal">
            <h2>Ready for Your Next Sweet Bite?</h2>
            <p>Treat yourself to our delicious Graham Bars today.</p>
            <a href="#contact" class="btn btn-primary">Order Your Graham Bars</a>
        </div>
    </section>

    <!-- ================= FAQ ================= -->
    <section class="faq section-pad" id="faq">
        <div class="container">
            <div class="section-head reveal">
                <span class="eyebrow">Good to Know</span>
                <h2>Frequently Asked Questions</h2>
            </div>

            <div class="faq-list">
                <?php
                $faqs = [
                    ['q' => 'How much are the Graham Bars?', 'a' => 'Prices start at ₱' . $product['price_from'] . ' for 1 piece. See the Product section above for our full size and price list.'],
                    ['q' => 'What sizes are available?', 'a' => 'We offer 1 Piece, 2 Pieces, and 4 Pieces — perfect for a quick snack or for sharing.'],
                    ['q' => 'Do you offer delivery?', 'a' => 'Yes! We coordinate delivery within our local area and can arrange meet-ups or courier booking for farther locations.'],
                    ['q' => 'How should I store the Graham Bars?', 'a' => 'Keep them chilled in the refrigerator and consume within a few days for the best taste and texture.'],
                    ['q' => 'Can I order in bulk?', 'a' => 'Absolutely. Message us ahead of time for bulk or event orders so we can prepare enough for your occasion.'],
                    ['q' => 'How can I place an order?', 'a' => 'Simply message us on Facebook, Messenger, or give us a call — we will guide you through the rest.'],
                ];
                foreach ($faqs as $index => $faq):
                ?>
                <div class="faq-item<?php echo $index === 0 ? ' active' : ''; ?>">
                    <button type="button" class="faq-question">
                        <span><?php echo htmlspecialchars($faq['q']); ?></span>
                        <span class="faq-icon">+</span>
                    </button>
                    <div class="faq-answer">
                        <p><?php echo htmlspecialchars($faq['a']); ?></p>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- ================= CONTACT ================= -->
    <section class="contact-section section-pad" id="contact">
        <div class="container contact-inner">
            <div class="reveal">
                <span class="eyebrow">Get in Touch</span>
                <h2>Let's Get You Some Graham Bars</h2>

                <ul class="contact-list">
                    <li>
                        <div class="contact-icon"><i class="fa-brands fa-facebook-f"></i></div>
                        <div>
                            <strong>Facebook</strong>
                            <span><?php echo htmlspecialchars($contact['facebook']); ?></span>
                        </div>
                    </li>
                    <li>
                        <div class="contact-icon"><i class="fa-brands fa-facebook-messenger"></i></div>
                        <div>
                            <strong>Messenger</strong>
                            <span><?php echo htmlspecialchars($contact['messenger']); ?></span>
                        </div>
                    </li>
                    <li>
                        <div class="contact-icon"><i class="fa-solid fa-phone"></i></div>
                        <div>
                            <strong>Phone</strong>
                            <span><?php echo htmlspecialchars($contact['phone']); ?></span>
                        </div>
                    </li>
                    <li>
                        <div class="contact-icon"><i class="fa-solid fa-envelope"></i></div>
                        <div>
                            <strong>Email</strong>
                            <span><?php echo htmlspecialchars($contact['email']); ?></span>
                        </div>
                    </li>
                    <li>
                        <div class="contact-icon"><i class="fa-solid fa-location-dot"></i></div>
                        <div>
                            <strong>Location</strong>
                            <span><?php echo htmlspecialchars($contact['location']); ?></span>
                        </div>
                    </li>
                </ul>

                <div class="contact-actions">
                    <a href="https://<?php echo htmlspecialchars($contact['messenger']); ?>" class="btn btn-primary" target="_blank" rel="noopener">Message Us</a>
                    <a href="tel:<?php echo htmlspecialchars(str_replace(' ', '', $contact['phone'])); ?>" class="btn btn-outline">Order Now</a>
                </div>
            </div>

            <div class="contact-visual reveal reveal-delay-1">
                <h3>Send an Order Inquiry</h3>
                <p class="form-intro">Fill this in and it's saved straight to our order list — we'll reach out to confirm.</p>

                <?php if ($orderFeedback['type']): ?>
                    <div class="form-alert form-alert-<?php echo htmlspecialchars($orderFeedback['type']); ?>" role="status">
                        <?php echo htmlspecialchars($orderFeedback['message']); ?>
                        <?php if ($orderFeedback['type'] === 'success' && $orderFeedback['order_id']): ?>
                            <div style="margin-top:12px;">
                                <a href="pay.php?order_id=<?php echo (int) $orderFeedback['order_id']; ?>" class="btn btn-primary">
                                    Scan to Pay via GCash / Maya / Bank
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

                <form class="order-form" method="POST" action="#contact">
                    <div class="form-group">
                        <label for="customer_name">Full Name</label>
                        <input type="text" id="customer_name" name="customer_name" placeholder="Karl Samonte"
                               value="<?php echo htmlspecialchars($name ?? ''); ?>" required maxlength="120">
                    </div>

                    <div class="form-group">
                        <label for="contact_info">Phone or Email</label>
                        <input type="text" id="contact_info" name="contact_info" placeholder="you@gmail.com"
                               value="<?php echo htmlspecialchars($contactInfo ?? ''); ?>" required maxlength="150">
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="order_size">Size</label>
                            <select id="order_size" name="order_size" required>
                                <option value="">Select size</option>
                                <?php foreach ($product['sizes'] as $sizeOption): ?>
                                    <option value="<?php echo htmlspecialchars($sizeOption['label']); ?>"
                                        <?php echo (isset($size) && $size === $sizeOption['label']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($sizeOption['label']); ?> — ₱<?php echo htmlspecialchars($sizeOption['price']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="order_flavor">Flavor</label>
                            <select id="order_flavor" name="order_flavor" required>
                                <option value="">Select flavor</option>
                                <?php foreach ($product['flavors'] as $flavorOption): ?>
                                    <option value="<?php echo htmlspecialchars($flavorOption['label']); ?>"
                                        <?php echo (isset($flavor) && $flavor === $flavorOption['label']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($flavorOption['label']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="order_qty">Quantity</label>
                            <input type="number" id="order_qty" name="order_qty" min="1" max="50"
                                   value="<?php echo htmlspecialchars((string) ($qty ?? 1)); ?>" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="order_message">Message (optional)</label>
                        <textarea id="order_message" name="order_message" rows="3" placeholder="Preferred pickup date, delivery notes, etc."><?php echo htmlspecialchars($message ?? ''); ?></textarea>
                    </div>

                    <button type="submit" name="order_inquiry" value="1" class="btn btn-primary btn-block">Submit Inquiry</button>
                </form>
            </div>
        </div>
    </section>

    <!-- ================= FOOTER ================= -->
    <footer class="footer">
        <div class="container footer-top">
            <div>
                <a href="#" class="logo">
                    <span class="logo-mark" aria-hidden="true"></span>
                    <?php echo htmlspecialchars($site['brand']); ?>
                </a>
                <p class="footer-tagline"><?php echo htmlspecialchars($site['tagline']); ?></p>
            </div>

            <div>
                <div class="footer-heading">Explore</div>
                <ul class="footer-links">
                    <li><a href="#home">Home</a></li>
                    <li><a href="#product">Product</a></li>
                    <li><a href="#reviews">Reviews</a></li>
                    <li><a href="#faq">FAQ</a></li>
                    <li><a href="#contact">Contact</a></li>
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
            &copy; <?php echo htmlspecialchars($site['year']); ?> <?php echo htmlspecialchars($site['brand']); ?>. All Rights Reserved.
        </div>
    </footer>

    <script src="js/script.js"></script>
</body>
</html>