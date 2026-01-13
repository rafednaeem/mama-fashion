<?php
$pageTitle = 'Products';
require_once __DIR__ . '/admin_header.php';

$pdo = getPDO();

$stmt = $pdo->query('SELECT p.*, c.name AS category_name 
                     FROM products p 
                     JOIN categories c ON p.category_id = c.id 
                     ORDER BY p.created_at DESC');
$products = $stmt->fetchAll();
?>

<section class="section">
    <div class="section-header">
        <div>
            <h1 class="section-title">Products</h1>
            <p class="section-subtitle">Manage catalog items, images and categories.</p>
        </div>
        <a href="<?php echo $baseUrl; ?>/admin/product_form.php" class="btn btn-primary btn-small">Add new product</a>
    </div>

    <div class="card">
        <table class="table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Category</th>
                    <th>Price</th>
                    <th>Stock</th>
                    <th>Status</th>
                    <th class="align-right">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($products as $p): ?>
                    <tr>
                        <td><?php echo e($p['name']); ?></td>
                        <td><?php echo e($p['category_name']); ?></td>
                        <td>Rs <?php echo number_format($p['price']); ?></td>
                        <td>
                            <?php if ($p['stock'] == 0): ?>
                                <span class="badge badge-danger">Out of Stock</span>
                            <?php elseif ($p['stock'] <= 10): ?>
                                <span class="badge badge-warning"><?php echo $p['stock']; ?> items</span>
                            <?php else: ?>
                                <span class="badge badge-success"><?php echo $p['stock']; ?> items</span>
                            <?php endif; ?>
                        </td>
                        <td><?php echo $p['is_active'] ? 'Active' : 'Hidden'; ?></td>
                        <td class="align-right">
                            <a href="<?php echo $baseUrl; ?>/admin/product_form.php?id=<?php echo (int)$p['id']; ?>" class="btn btn-ghost btn-small">Edit</a>
                            <a href="<?php echo $baseUrl; ?>/admin/delete_product.php?id=<?php echo (int)$p['id']; ?>" class="btn btn-ghost btn-small" onclick="return confirm('Delete this product?');">Delete</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>

<style>
.badge {
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 12px;
    font-weight: bold;
}

.badge-success {
    background-color: #28a745;
    color: white;
}

.badge-warning {
    background-color: #ffc107;
    color: #212529;
}

.badge-danger {
    background-color: #dc3545;
    color: white;
}
</style>

<?php
require_once __DIR__ . '/admin_footer.php';

