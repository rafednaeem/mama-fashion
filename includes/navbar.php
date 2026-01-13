<?php
// includes/navbar.php
$categories = $categories ?? getCategories();
?>
<header class="site-header">
    <div class="container nav-inner">
        <a href="<?php echo $baseUrl; ?>/" class="brand">
            <span class="brand-mark">M</span>
            <span class="brand-text">
                MAMA <span>Fashion</span>
            </span>
        </a>

        <button class="nav-toggle" aria-label="Toggle menu">
            <span></span><span></span><span></span>
        </button>

        <nav class="nav-links">
            <a href="<?php echo $baseUrl; ?>/" class="nav-link">Home</a>
            <div class="nav-dropdown">
                <button class="nav-link nav-link-dropdown">Categories</button>
                <div class="nav-dropdown-menu">
                    <?php foreach ($categories as $cat): ?>
                        <a href="<?php echo $baseUrl; ?>/products.php?category=<?php echo (int)$cat['id']; ?>">
                            <?php echo e($cat['name']); ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
            <a href="<?php echo $baseUrl; ?>/products.php" class="nav-link">All Products</a>
        </nav>

        <div class="nav-actions">
            <?php if (isUserLoggedIn()): ?>
                <?php
                $uid = (int)($_SESSION['user_id'] ?? 0);
                $wishlistCount = getWishlistCount($uid);
                $unreadNotif   = getUnreadNotificationCount($uid);
                ?>
                <a href="<?php echo $baseUrl; ?>/my_orders.php" class="btn btn-ghost">My Orders</a>
                <a href="<?php echo $baseUrl; ?>/my_wishlist.php" class="btn btn-ghost">
                    Wishlist (<?php echo $wishlistCount; ?>)
                </a>
                <a href="<?php echo $baseUrl; ?>/notifications.php" class="cart-pill">
                    Alerts
                    <span class="cart-count">
                        <?php echo $unreadNotif; ?>
                    </span>
                </a>
                <span class="nav-user">Hi, <?php echo e($_SESSION['user_name'] ?? 'Guest'); ?></span>
                <a href="<?php echo $baseUrl; ?>/logout.php" class="btn btn-ghost">Logout</a>
            <?php else: ?>
                <a href="<?php echo $baseUrl; ?>/login.php" class="btn btn-ghost">Login</a>
                <a href="<?php echo $baseUrl; ?>/register.php" class="btn btn-primary">Sign Up</a>
            <?php endif; ?>
            <a href="<?php echo $baseUrl; ?>/cart.php" class="cart-pill">
                Cart
                <span class="cart-count">
                    <?php echo count(getCartItems()); ?>
                </span>
            </a>
        </div>
    </div>
</header>

