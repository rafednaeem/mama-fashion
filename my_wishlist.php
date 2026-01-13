<?php
require_once __DIR__ . '/includes/header.php';

if (!isUserLoggedIn()) {
    redirect('/login.php');
}

$pageTitle = 'My Wishlist';
$items = getWishlistItems((int)$_SESSION['user_id']);
?>

<section class="section">
    <div class="container">
        <div class="section-header">
            <div>
                <h1 class="section-title">My wishlist</h1>
                <p class="section-subtitle">
                    Save your favourite suits, jewelry and accessories to watch for price drops and sales.
                </p>
            </div>
        </div>

        <?php if (empty($items)): ?>
            <p class="section-subtitle">Your wishlist is empty. Browse products and tap “Add to wishlist”.</p>
        <?php else: ?>
            <div class="product-grid">
                <?php foreach ($items as $item): ?>
                    <article class="product-card">
                        <a href="<?php echo $baseUrl; ?>/product_detail.php?id=<?php echo (int)$item['product_id']; ?>">
                            <div class="product-image">
                                <?php if (!empty($item['image'])): ?>
                                    <img src="<?php echo $baseUrl . '/uploads/products/' . e($item['image']); ?>" alt="<?php echo e($item['name']); ?>">
                                <?php endif; ?>
                            </div>
                        </a>
                        <div class="product-body">
                            <p class="product-category">
                                <?php echo e($item['category_name']); ?>
                                <?php if (!empty($item['is_on_sale'])): ?>
                                    <span class="chip-pill" style="margin-left:6px; background:#fef3c7; border-color:#facc15; color:#92400e;">
                                        On sale
                                    </span>
                                <?php endif; ?>
                            </p>
                            <h3 class="product-name"><?php echo e($item['name']); ?></h3>
                            <div class="product-price-row">
                                <?php if (!empty($item['is_on_sale']) && $item['sale_price'] !== null): ?>
                                    <span class="product-price">
                                        <span style="text-decoration:line-through; opacity:0.7; margin-right:6px;">
                                            Rs <?php echo number_format($item['price']); ?>
                                        </span>
                                        <span style="color:#b91c1c; font-weight:600;">
                                            Rs <?php echo number_format($item['sale_price']); ?>
                                        </span>
                                    </span>
                                <?php else: ?>
                                    <span class="product-price">Rs <?php echo number_format($item['price']); ?></span>
                                <?php endif; ?>
                            </div>
                            <div class="product-action" style="margin-top:8px; display:flex; gap:6px;">
                                <a href="<?php echo $baseUrl; ?>/add_to_cart.php?id=<?php echo (int)$item['product_id']; ?>" class="btn btn-primary btn-small">
                                    Add to cart
                                </a>
                                <a href="<?php echo $baseUrl; ?>/remove_from_wishlist.php?id=<?php echo (int)$item['product_id']; ?>" class="btn btn-ghost btn-small">
                                    Remove
                                </a>
                            </div>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php
require_once __DIR__ . '/includes/footer.php';

