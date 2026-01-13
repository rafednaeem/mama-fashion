<?php
$pageTitle = 'Dashboard';
require_once __DIR__ . '/admin_header.php';

$pdo = getPDO();

$totalProducts = (int)$pdo->query('SELECT COUNT(*) FROM products')->fetchColumn();
$totalOrders = (int)$pdo->query('SELECT COUNT(*) FROM orders')->fetchColumn();
$pendingOrders = (int)$pdo->query("SELECT COUNT(*) FROM orders WHERE status = 'pending'")->fetchColumn();
$totalUsers = (int)$pdo->query('SELECT COUNT(*) FROM users')->fetchColumn();
?>

<section class="section">
    <div class="section-header">
        <div>
            <h1 class="section-title">Admin dashboard</h1>
            <p class="section-subtitle">
                Quick overview of your MAMA Fashion store.
            </p>
        </div>
    </div>

    <div class="product-grid">
        <div class="card">
            <h2 class="card-title">Products</h2>
            <p class="section-subtitle">Total products in catalog.</p>
            <p style="font-size:1.4rem;font-weight:600;"><?php echo $totalProducts; ?></p>
        </div>
        <div class="card">
            <h2 class="card-title">Orders</h2>
            <p class="section-subtitle">Total orders so far.</p>
            <p style="font-size:1.4rem;font-weight:600;"><?php echo $totalOrders; ?></p>
        </div>
        <div class="card">
            <h2 class="card-title">Pending orders</h2>
            <p class="section-subtitle">Waiting for confirmation / delivery.</p>
            <p style="font-size:1.4rem;font-weight:600;"><?php echo $pendingOrders; ?></p>
        </div>
        <div class="card">
            <h2 class="card-title">Registered users</h2>
            <p class="section-subtitle">Customers with accounts.</p>
            <p style="font-size:1.4rem;font-weight:600;"><?php echo $totalUsers; ?></p>
        </div>
    </div>
</section>

<?php
require_once __DIR__ . '/admin_footer.php';

