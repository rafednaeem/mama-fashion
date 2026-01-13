<?php
$pageTitle = 'Orders';
require_once __DIR__ . '/admin_header.php';

$pdo = getPDO();

$stmt = $pdo->query('SELECT * FROM orders ORDER BY created_at DESC');
$orders = $stmt->fetchAll();
?>

<section class="section">
    <div class="section-header">
        <div>
            <h1 class="section-title">Orders</h1>
            <p class="section-subtitle">Review, verify payments and update statuses.</p>
        </div>
    </div>

    <div class="card">
        <table class="table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Customer</th>
                    <th>Phone</th>
                    <th>Payment</th>
                    <th>Status</th>
                    <th>Total</th>
                    <th class="align-right">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($orders as $order): ?>
                    <tr>
                        <td><?php echo (int)$order['id']; ?></td>
                        <td><?php echo e($order['customer_name']); ?></td>
                        <td><?php echo e($order['phone']); ?></td>
                        <td><?php echo e($order['payment_method']); ?> (<?php echo e($order['payment_status']); ?>)</td>
                        <td><?php echo e($order['status']); ?></td>
                        <td>Rs <?php echo number_format($order['total_amount']); ?></td>
                        <td class="align-right">
                            <a href="<?php echo $baseUrl; ?>/admin/order_view.php?id=<?php echo (int)$order['id']; ?>" class="btn btn-ghost btn-small">View</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>

<?php
require_once __DIR__ . '/admin_footer.php';

