<?php
$pageTitle = 'Order details';
require_once __DIR__ . '/admin_header.php';

$pdo = getPDO();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    echo '<p class="section-subtitle">Order not found.</p>';
    require_once __DIR__ . '/admin_footer.php';
    exit;
}

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['csrf_token'] ?? '';
    if (verifyCsrfToken($token)) {
        $status = $_POST['status'] ?? 'pending';
        $paymentStatus = $_POST['payment_status'] ?? 'pending';

        $stmt = $pdo->prepare('UPDATE orders SET status = :status, payment_status = :payment_status WHERE id = :id');
        $stmt->execute([
            ':status'         => $status,
            ':payment_status' => $paymentStatus,
            ':id'             => $id,
        ]);
    }
}

$stmt = $pdo->prepare('SELECT * FROM orders WHERE id = :id');
$stmt->execute([':id' => $id]);
$order = $stmt->fetch();

if (!$order) {
    echo '<p class="section-subtitle">Order not found.</p>';
    require_once __DIR__ . '/admin_footer.php';
    exit;
}

$stmtItems = $pdo->prepare('SELECT oi.*, p.name FROM order_items oi JOIN products p ON oi.product_id = p.id WHERE oi.order_id = :order_id');
$stmtItems->execute([':order_id' => $id]);
$items = $stmtItems->fetchAll();

?>

<section class="section">
    <div class="section-header">
        <div>
            <h1 class="section-title">Order #<?php echo (int)$order['id']; ?></h1>
            <p class="section-subtitle">Review items, customer details and payment proof.</p>
        </div>
    </div>

    <div class="cart-layout">
        <div class="card">
            <h2 class="card-title">Customer &amp; delivery</h2>
            <p class="section-subtitle">
                <strong>Name:</strong> <?php echo e($order['customer_name']); ?><br>
                <strong>Phone:</strong> <?php echo e($order['phone']); ?><br>
                <strong>Address:</strong><br>
                <?php echo nl2br(e($order['address'])); ?>
            </p>

            <h2 class="card-title">Payment</h2>
            <p class="section-subtitle">
                <strong>Method:</strong> <?php echo e($order['payment_method']); ?><br>
                <strong>Payment status:</strong> <?php echo e($order['payment_status']); ?><br>
                <strong>Order status:</strong> <?php echo e($order['status']); ?><br>
                <strong>Total:</strong> Rs <?php echo number_format($order['total_amount']); ?>
            </p>

            <?php if (!empty($order['payment_proof'])): ?>
                <div class="form-group" style="margin-top:10px;">
                    <label>Payment proof</label>
                    <p class="form-help">
                        Screenshot uploaded by customer. Click to open in a new tab.
                    </p>
                    <a href="<?php echo $baseUrl . '/uploads/payments/' . e($order['payment_proof']); ?>" target="_blank" class="btn btn-ghost btn-small">
                        View screenshot
                    </a>
                </div>
            <?php else: ?>
                <p class="form-help" style="margin-top:8px;">
                    No payment screenshot uploaded (likely Cash on Delivery).
                </p>
            <?php endif; ?>
        </div>

        <div class="card">
            <h2 class="card-title">Items</h2>
            <table class="table">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Qty</th>
                        <th class="align-right">Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($items as $it): ?>
                        <tr>
                            <td><?php echo e($it['name']); ?></td>
                            <td><?php echo (int)$it['quantity']; ?></td>
                            <td class="align-right">Rs <?php echo number_format($it['total_price']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="2" class="align-right">Grand total</td>
                        <td class="align-right"><strong>Rs <?php echo number_format($order['total_amount']); ?></strong></td>
                    </tr>
                </tfoot>
            </table>

            <h2 class="card-title" style="margin-top:10px;">Update status</h2>
            <form method="post">
                <input type="hidden" name="csrf_token" value="<?php echo e(getCsrfToken()); ?>">
                <div class="form-group">
                    <label for="payment_status">Payment status</label>
                    <select id="payment_status" name="payment_status" class="form-control">
                        <?php
                        $paymentStatuses = ['pending', 'verified', 'rejected'];
                        foreach ($paymentStatuses as $st):
                        ?>
                            <option value="<?php echo $st; ?>" <?php echo $order['payment_status'] === $st ? 'selected' : ''; ?>>
                                <?php echo ucfirst($st); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="status">Order status</label>
                    <select id="status" name="status" class="form-control">
                        <?php
                        $orderStatuses = ['pending', 'confirmed', 'delivered', 'cancelled'];
                        foreach ($orderStatuses as $st):
                        ?>
                            <option value="<?php echo $st; ?>" <?php echo $order['status'] === $st ? 'selected' : ''; ?>>
                                <?php echo ucfirst($st); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary btn-small">Save changes</button>
            </form>
        </div>
    </div>
</section>

<?php
require_once __DIR__ . '/admin_footer.php';

