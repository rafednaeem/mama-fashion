<?php
require_once __DIR__ . '/includes/header.php';

$pageTitle = 'Cart';

$cartItems = getCartItems();
$detailedItems = [];
$grandTotal = 0;

if (!empty($cartItems)) {
    $pdo = getPDO();
    $ids = array_keys($cartItems);
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $stmt = $pdo->prepare("SELECT id, name, price FROM products WHERE id IN ($placeholders)");
    $stmt->execute($ids);
    $productsMap = [];
    foreach ($stmt->fetchAll() as $p) {
        $productsMap[$p['id']] = $p;
    }

    foreach ($cartItems as $pid => $item) {
        if (!isset($productsMap[$pid])) {
            continue;
        }
        $p = $productsMap[$pid];
        $qty = (int)$item['qty'];
        $total = $qty * (float)$p['price'];
        $grandTotal += $total;
        $detailedItems[] = [
            'id'    => $pid,
            'name'  => $p['name'],
            'price' => $p['price'],
            'qty'   => $qty,
            'total' => $total,
        ];
    }
}
?>

<section class="section">
    <div class="container">
        <div class="section-header">
            <div>
                <h1 class="section-title">Your cart</h1>
                <p class="section-subtitle">Review your items before checkout.</p>
            </div>
        </div>

        <?php if (empty($detailedItems)): ?>
            <p class="section-subtitle">Your cart is empty. Start exploring our collection.</p>
        <?php else: ?>
            <div class="cart-layout">
                <div class="card">
                    <h2 class="card-title">Items</h2>
                    <form action="<?php echo $baseUrl; ?>/update_cart.php" method="post">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>Price</th>
                                    <th>Qty</th>
                                    <th class="align-right">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($detailedItems as $item): ?>
                                    <tr>
                                        <td><?php echo e($item['name']); ?></td>
                                        <td>Rs <?php echo number_format($item['price']); ?></td>
                                        <td>
                                            <input
                                                type="number"
                                                name="qty[<?php echo (int)$item['id']; ?>]"
                                                value="<?php echo (int)$item['qty']; ?>"
                                                min="0"
                                                class="form-control"
                                                style="width:60px;"
                                            >
                                        </td>
                                        <td class="align-right">
                                            Rs <?php echo number_format($item['total']); ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="3" class="align-right">Grand total</td>
                                    <td class="align-right"><strong>Rs <?php echo number_format($grandTotal); ?></strong></td>
                                </tr>
                            </tfoot>
                        </table>
                        <button type="submit" class="btn btn-ghost btn-small" style="margin-top:8px;">
                            Update quantities
                        </button>
                    </form>
                </div>

                <div class="card">
                    <h2 class="card-title">Next step</h2>
                    <p class="section-subtitle" style="margin-bottom:8px;">
                        Checkout using Cash on Delivery or bank / wallet transfer
                        with screenshot upload.
                    </p>
                    <a href="<?php echo $baseUrl; ?>/checkout.php" class="btn btn-primary btn-small">
                        Proceed to checkout
                    </a>
                </div>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php
require_once __DIR__ . '/includes/footer.php';

