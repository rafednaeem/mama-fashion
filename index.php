<?php
require_once __DIR__ . '/includes/header.php';

// Fetch categories and featured products for the home page.
$categories = getCategories();
// Show latest 8 products, no filters.
$featuredProducts = getProducts(null, 8);
?>

<section class="hero">
    <div class="container hero-inner">
        <div class="hero-copy">
            <div class="eyebrow">
                <span class="eyebrow-dot"></span>
                Curated Pakistani &amp; South Asian women’s fashion
            </div>
            <h1 class="hero-title">
                <span>MAMA</span> Fashion Studio
            </h1>
            <p class="hero-subtitle">
                A small but thoughtful collection of stitched &amp; unstitched suits,
                kurtis, artificial jewelry and everyday accessories – crafted for
                the modern Pakistani woman.
            </p>
            <div class="hero-cta">
                <a href="<?php echo $baseUrl; ?>/products.php" class="btn btn-primary">
                    Explore collection
                </a>
                <a href="<?php echo $baseUrl; ?>/register.php" class="btn btn-ghost">
                    Create account
                </a>
            </div>
            <p class="hero-note">
                Free delivery on orders within Pakistan over Rs 3,000. Cash on
                delivery &amp; bank transfer available.
            </p>
            <div class="hero-badge-row">
                <span class="hero-badge">Pret &amp; unstitched suits</span>
                <span class="hero-badge">Artificial kundan &amp; polki</span>
                <span class="hero-badge">Dupattas, bags &amp; more</span>
            </div>
        </div>
        <div class="hero-visual">
            <div class="hero-card">
                <div class="hero-card-title">Festive Edit ’26</div>
                <div class="hero-card-sub">
                    Rich maroons, emerald greens &amp; antique gold jewelry
                    inspired by Lahore &amp; Karachi bazaars.
                </div>
                <div class="hero-chips">
                    <span class="hero-chip">3‑piece lawn suits</span>
                    <span class="hero-chip">Hand‑picked jhumkay</span>
                    <span class="hero-chip">Formal shawls</span>
                    <span class="hero-chip">Wedding accessories</span>
                </div>
            </div>
            <div class="hero-float">
                Curated for <strong>Pakistani &amp; South Asian</strong> women –
                styled for real events: dawats, weddings, Eid &amp; casual days.
            </div>
        </div>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="section-header">
            <div>
                <h2 class="section-title">Shop by category</h2>
                <p class="section-subtitle">From stitched suits to artificial jewelry and accessories.</p>
            </div>
        </div>
        <div class="chip-row">
            <?php foreach ($categories as $cat): ?>
                <a class="chip-pill" href="<?php echo $baseUrl; ?>/products.php?category=<?php echo (int)$cat['id']; ?>">
                    <?php echo e($cat['name']); ?>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="section-header">
            <div>
                <h2 class="section-title">Featured pieces</h2>
                <p class="section-subtitle">A small preview of what you can stock your wardrobe with.</p>
            </div>
            <a href="<?php echo $baseUrl; ?>/products.php" class="link-quiet">View all products →</a>
        </div>

        <div class="product-grid">
            <?php foreach ($featuredProducts as $product): ?>
                <article class="product-card">
                    <a href="<?php echo $baseUrl; ?>/product_detail.php?id=<?php echo (int)$product['id']; ?>">
                        <div class="product-image">
                            <?php if (!empty($product['image'])): ?>
                                <img src="<?php echo $baseUrl . '/uploads/products/' . e($product['image']); ?>" alt="<?php echo e($product['name']); ?>">
                            <?php endif; ?>
                        </div>
                    </a>
                    <div class="product-body">
                        <p class="product-category"><?php echo e($product['category_name']); ?></p>
                        <h3 class="product-name"><?php echo e($product['name']); ?></h3>
                        <div class="product-price-row">
                            <?php if (!empty($product['is_on_sale']) && $product['sale_price'] !== null): ?>
                                <span class="product-price">
                                    <span style="text-decoration:line-through; opacity:0.7; margin-right:6px;">
                                        Rs <?php echo number_format($product['price']); ?>
                                    </span>
                                    <span style="color:#b91c1c; font-weight:600;">
                                        Rs <?php echo number_format($product['sale_price']); ?>
                                    </span>
                                </span>
                            <?php else: ?>
                                <span class="product-price">Rs <?php echo number_format($product['price']); ?></span>
                            <?php endif; ?>
                            
                            <?php if (isset($product['stock']) && $product['stock'] > 0): ?>
                                <a href="<?php echo $baseUrl; ?>/add_to_cart.php?id=<?php echo (int)$product['id']; ?>&source=home" class="btn btn-primary btn-small">
                                    Add to cart
                                </a>
                            <?php else: ?>
                                <button class="btn btn-disabled btn-small" disabled>
                                    Out of Stock
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<?php
require_once __DIR__ . '/includes/footer.php';

