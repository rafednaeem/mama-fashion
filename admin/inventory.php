<?php
$pageTitle = 'Inventory Management';
require_once __DIR__ . '/admin_header.php';

$pdo = getPDO();

// Handle stock updates
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_stock'])) {
    $productId = (int)$_POST['product_id'];
    $newStock = (int)$_POST['stock'];
    
    $stmt = $pdo->prepare('UPDATE products SET stock = :stock WHERE id = :id');
    $stmt->execute([':stock' => $newStock, ':id' => $productId]);
    
    $success = 'Stock updated successfully!';
}

// Get filter parameters
$categoryFilter = isset($_GET['category']) ? (int)$_GET['category'] : 0;
$stockFilter = isset($_GET['stock_filter']) ? $_GET['stock_filter'] : '';

// Build query
$whereClause = '';
$params = [];

if ($categoryFilter > 0) {
    $whereClause .= ' AND p.category_id = :category_id';
    $params[':category_id'] = $categoryFilter;
}

if ($stockFilter === 'low') {
    $whereClause .= ' AND p.stock <= 10';
} elseif ($stockFilter === 'out') {
    $whereClause .= ' AND p.stock = 0';
}

$query = "SELECT p.*, c.name AS category_name 
          FROM products p 
          JOIN categories c ON p.category_id = c.id 
          WHERE 1=1 $whereClause
          ORDER BY p.stock ASC, p.name ASC";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$products = $stmt->fetchAll();

// Get categories for filter
$categories = getCategories();
?>

<section class="section">
    <div class="section-header">
        <div>
            <h1 class="section-title">Inventory Management</h1>
            <p class="section-subtitle">Manage product stock levels and inventory.</p>
        </div>
        <a href="<?php echo $baseUrl; ?>/admin/product_form.php" class="btn btn-primary btn-small">Add new product</a>
    </div>

    <?php if (isset($success)): ?>
        <div class="alert alert-success"><?php echo e($success); ?></div>
    <?php endif; ?>

    <!-- Filters -->
    <div class="card">
        <form method="GET" class="form-inline">
            <div class="form-group">
                <label for="category">Category:</label>
                <select name="category" id="category" class="form-control">
                    <option value="">All Categories</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?php echo $cat['id']; ?>" <?php echo $categoryFilter == $cat['id'] ? 'selected' : ''; ?>>
                            <?php echo e($cat['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label for="stock_filter">Stock Status:</label>
                <select name="stock_filter" id="stock_filter" class="form-control">
                    <option value="">All Products</option>
                    <option value="low" <?php echo $stockFilter === 'low' ? 'selected' : ''; ?>>Low Stock (≤10)</option>
                    <option value="out" <?php echo $stockFilter === 'out' ? 'selected' : ''; ?>>Out of Stock</option>
                </select>
            </div>
            
            <button type="submit" class="btn btn-primary">Filter</button>
            <a href="<?php echo $baseUrl; ?>/admin/inventory.php" class="btn btn-ghost">Clear</a>
        </form>
    </div>

    <!-- Inventory Summary -->
    <div class="grid grid-3">
        <div class="card">
            <h3>Total Products</h3>
            <p class="text-large"><?php echo count($products); ?></p>
        </div>
        <div class="card">
            <h3>Low Stock Items</h3>
            <p class="text-large text-warning">
                <?php 
                $lowStock = array_filter($products, fn($p) => $p['stock'] > 0 && $p['stock'] <= 10);
                echo count($lowStock);
                ?>
            </p>
        </div>
        <div class="card">
            <h3>Out of Stock</h3>
            <p class="text-large text-danger">
                <?php 
                $outOfStock = array_filter($products, fn($p) => $p['stock'] == 0);
                echo count($outOfStock);
                ?>
            </p>
        </div>
    </div>

    <!-- Products Table -->
    <div class="card">
        <table class="table">
            <thead>
                <tr>
                    <th>Product Name</th>
                    <th>Category</th>
                    <th>Price</th>
                    <th>Stock</th>
                    <th>Status</th>
                    <th>Stock Level</th>
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
                            <form method="POST" class="form-inline stock-update-form">
                                <input type="hidden" name="product_id" value="<?php echo $p['id']; ?>">
                                <input type="number" name="stock" value="<?php echo $p['stock']; ?>" 
                                       min="0" class="form-control" style="width: 80px;">
                                <button type="submit" name="update_stock" class="btn btn-small btn-primary">Update</button>
                            </form>
                        </td>
                        <td><?php echo $p['is_active'] ? 'Active' : 'Hidden'; ?></td>
                        <td>
                            <?php if ($p['stock'] == 0): ?>
                                <span class="badge badge-danger">Out of Stock</span>
                            <?php elseif ($p['stock'] <= 10): ?>
                                <span class="badge badge-warning">Low Stock</span>
                            <?php else: ?>
                                <span class="badge badge-success">In Stock</span>
                            <?php endif; ?>
                        </td>
                        <td class="align-right">
                            <a href="<?php echo $baseUrl; ?>/admin/product_form.php?id=<?php echo $p['id']; ?>" 
                               class="btn btn-ghost btn-small">Edit</a>
                            <a href="<?php echo $baseUrl; ?>/admin/delete_product.php?id=<?php echo $p['id']; ?>" 
                               class="btn btn-ghost btn-small" onclick="return confirm('Delete this product?');">Delete</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>

<style>
.stock-update-form {
    display: flex;
    gap: 5px;
    align-items: center;
}

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

.text-large {
    font-size: 24px;
    font-weight: bold;
    margin: 0;
}

.text-warning {
    color: #ffc107;
}

.text-danger {
    color: #dc3545;
}

.form-inline {
    display: flex;
    gap: 15px;
    align-items: center;
    flex-wrap: wrap;
}

.form-group {
    display: flex;
    align-items: center;
    gap: 8px;
}

.form-group label {
    margin: 0;
    font-weight: 500;
}

.grid {
    display: grid;
    gap: 20px;
    margin-bottom: 20px;
}

.grid-3 {
    grid-template-columns: repeat(3, 1fr);
}

@media (max-width: 768px) {
    .grid-3 {
        grid-template-columns: 1fr;
    }
    
    .form-inline {
        flex-direction: column;
        align-items: stretch;
    }
    
    .stock-update-form {
        flex-direction: column;
        align-items: stretch;
    }
}
</style>

<?php
require_once __DIR__ . '/admin_footer.php';
