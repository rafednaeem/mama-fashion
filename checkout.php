<?php
require_once __DIR__ . '/includes/header.php';

$pageTitle = 'Checkout';

$cartItems = getCartItems();
if (empty($cartItems)) {
    echo '<div class="container"><p class="section-subtitle" style="margin-top:24px;">Your cart is empty.</p></div>';
    require_once __DIR__ . '/includes/footer.php';
    exit;
}

$pdo = getPDO();

// Build cart details and total
$ids = array_keys($cartItems);
$placeholders = implode(',', array_fill(0, count($ids), '?'));
$stmt = $pdo->prepare("SELECT id, name, price FROM products WHERE id IN ($placeholders)");
$stmt->execute($ids);
$productsMap = [];
foreach ($stmt->fetchAll() as $p) {
    $productsMap[$p['id']] = $p;
}

$items = [];
$grandTotal = 0;
foreach ($cartItems as $pid => $item) {
    if (!isset($productsMap[$pid])) {
        continue;
    }
    $p = $productsMap[$pid];
    $qty = (int)$item['qty'];
    $total = $qty * (float)$p['price'];
    $grandTotal += $total;
    $items[] = [
        'id'    => $pid,
        'name'  => $p['name'],
        'price' => $p['price'],
        'qty'   => $qty,
        'total' => $total,
    ];
}

$customerName = $_SESSION['user_name'] ?? '';
$phone = '';
$address = '';
$paymentMethod = 'cod';
$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['csrf_token'] ?? '';
    if (!verifyCsrfToken($token)) {
        $errors[] = 'Security check failed. Please try again.';
    }

    $customerName = trim($_POST['customer_name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $paymentMethod = $_POST['payment_method'] ?? 'cod';

    if ($customerName === '') {
        $errors[] = 'Customer name is required.';
    }
    if ($phone === '') {
        $errors[] = 'Phone number is required.';
    }
    if ($address === '') {
        $errors[] = 'Delivery address is required.';
    }

    $paymentProofFile = null;
    if ($paymentMethod !== 'cod' && isset($_FILES['payment_proof']) && $_FILES['payment_proof']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = __DIR__ . '/uploads/payments/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        $tmp = $_FILES['payment_proof']['tmp_name'];
        $basename = basename($_FILES['payment_proof']['name']);
        $ext = strtolower(pathinfo($basename, PATHINFO_EXTENSION));
        if (!in_array($ext, ['jpg', 'jpeg', 'png', 'gif'])) {
            $errors[] = 'Payment proof must be an image (JPG, PNG, GIF).';
        } else {
            $safeName = 'pay_' . time() . '_' . preg_replace('/[^a-zA-Z0-9_\.-]/', '_', $basename);
            $dest = $uploadDir . $safeName;
            if (!move_uploaded_file($tmp, $dest)) {
                $errors[] = 'Failed to upload payment proof.';
            } else {
                $paymentProofFile = $safeName;
            }
        }
    }

    if (empty($errors)) {
        // Create order
        $stmt = $pdo->prepare('INSERT INTO orders 
            (user_id, customer_name, phone, address, payment_method, payment_status, status, total_amount, payment_proof)
            VALUES (:user_id, :customer_name, :phone, :address, :payment_method, :payment_status, :status, :total_amount, :payment_proof)
        ');
        $stmt->execute([
            ':user_id'        => $_SESSION['user_id'] ?? null,
            ':customer_name'  => $customerName,
            ':phone'          => $phone,
            ':address'        => $address,
            ':payment_method' => $paymentMethod,
            ':payment_status' => $paymentMethod === 'cod' ? 'pending' : 'pending',
            ':status'         => 'pending',
            ':total_amount'   => $grandTotal,
            ':payment_proof'  => $paymentProofFile,
        ]);
        $orderId = (int)$pdo->lastInsertId();

        // Insert items
        $stmtItem = $pdo->prepare('INSERT INTO order_items (order_id, product_id, quantity, unit_price, total_price)
                                   VALUES (:order_id, :product_id, :quantity, :unit_price, :total_price)');
        foreach ($items as $it) {
            $stmtItem->execute([
                ':order_id'   => $orderId,
                ':product_id' => $it['id'],
                ':quantity'   => $it['qty'],
                ':unit_price' => $it['price'],
                ':total_price'=> $it['total'],
            ]);
        }

        clearCart();
        $success = 'Thank you! Your order has been placed. Our team will verify your payment (if applicable) and confirm shortly.';
    }
}
?>

<section class="section">
    <div class="container">
        <div class="cart-layout">
            <div class="card">
                <h1 class="card-title">Checkout</h1>
                <p class="section-subtitle" style="margin-bottom:8px;">
                    Provide your delivery details and select a payment method.
                </p>

                <?php if (!empty($errors)): ?>
                    <div class="alert alert-error">
                        <?php foreach ($errors as $err): ?>
                            <div><?php echo e($err); ?></div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <?php if ($success): ?>
                    <div class="alert alert-success">
                        <?php echo e($success); ?>
                    </div>
                <?php else: ?>
                    <form method="post" enctype="multipart/form-data">
                        <input type="hidden" name="csrf_token" value="<?php echo e(getCsrfToken()); ?>">
                        <div class="form-group">
                            <label for="customer_name">Full name</label>
                            <input type="text" id="customer_name" name="customer_name" class="form-control" value="<?php echo e($customerName); ?>" required>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="phone">Phone (WhatsApp preferred)</label>
                                <input type="text" id="phone" name="phone" class="form-control" value="<?php echo e($phone); ?>" required>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="address">Full delivery address</label>
                            <textarea id="address" name="address" class="form-control" rows="3" required><?php echo e($address); ?></textarea>
                            <p class="form-help">
                                Include city, area, house number, and any landmark (e.g., DHA Phase 3, Lahore).
                            </p>
                        </div>

                        <div class="form-group">
                            <label>Payment method</label>
                            <select name="payment_method" class="form-control">
                                <option value="cod" <?php echo $paymentMethod === 'cod' ? 'selected' : ''; ?>>Cash on Delivery (COD)</option>
                                <option value="bank_transfer" <?php echo $paymentMethod === 'bank_transfer' ? 'selected' : ''; ?>>Manual Bank / Wallet Transfer</option>
                                <option value="jazzcash" <?php echo $paymentMethod === 'jazzcash' ? 'selected' : ''; ?>>JazzCash (manual)</option>
                                <option value="easypaisa" <?php echo $paymentMethod === 'easypaisa' ? 'selected' : ''; ?>>EasyPaisa (manual)</option>
                                <option value="sadapay" <?php echo $paymentMethod === 'sadapay' ? 'selected' : ''; ?>>SadaPay (manual)</option>
                                <option value="nayapay" <?php echo $paymentMethod === 'nayapay' ? 'selected' : ''; ?>>NayaPay (manual)</option>
                            </select>
                            <p class="form-help">
                                Online wallets are placeholder flows only – no live API integration yet.
                            </p>
                        </div>

                        <div class="form-group">
                            <label for="payment_proof">Payment screenshot (required for transfers)</label>
                            <input type="file" id="payment_proof" name="payment_proof" class="form-control" accept="image/*">
                            <p class="form-help">
                                For COD you may leave this empty. For transfers, upload a clear screenshot of your payment.
                            </p>
                        </div>

                        <button type="submit" class="btn btn-primary btn-small">Place order</button>
                    </form>
                <?php endif; ?>
            </div>

            <div class="card">
                <h2 class="card-title">Order summary</h2>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Item</th>
                            <th>Qty</th>
                            <th class="align-right">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($items as $it): ?>
                            <tr>
                                <td><?php echo e($it['name']); ?></td>
                                <td><?php echo (int)$it['qty']; ?></td>
                                <td class="align-right">Rs <?php echo number_format($it['total']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="2" class="align-right">Grand total</td>
                            <td class="align-right"><strong>Rs <?php echo number_format($grandTotal); ?></strong></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</section>

<?php
require_once __DIR__ . '/includes/footer.php';

