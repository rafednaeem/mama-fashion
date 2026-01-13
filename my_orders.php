<?php
require_once __DIR__ . '/includes/header.php';

if (!isUserLoggedIn()) {
    redirect('/login.php');
}

$pageTitle = 'My Orders';

$pdo = getPDO();
$userId = (int)$_SESSION['user_id'];

$stmt = $pdo->prepare('SELECT * FROM orders WHERE user_id = :uid ORDER BY created_at DESC');
$stmt->execute([':uid' => $userId]);
$orders = $stmt->fetchAll();
?>

<section class="section">
    <div class="container">
        <div class="section-header">
            <div>
                <h1 class="section-title">My orders</h1>
                <p class="section-subtitle">
                    Track the status of your recent purchases.
                </p>
            </div>
        </div>

        <?php if (empty($orders)): ?>
            <p class="section-subtitle">You have not placed any orders yet.</p>
        <?php else: ?>
            <div class="card">
                <table class="table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Date</th>
                            <th>Payment</th>
                            <th>Order status</th>
                            <th class="align-right">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($orders as $order): ?>
                            <tr>
                                <td><?php echo (int)$order['id']; ?></td>
                                <td><?php echo e($order['created_at']); ?></td>
                                <td><?php echo e($order['payment_method']); ?> (<?php echo e($order['payment_status']); ?>)</td>
                                <td><?php echo e($order['status']); ?></td>
                                <td class="align-right">Rs <?php echo number_format($order['total_amount']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php
require_once __DIR__ . '/includes/footer.php';

