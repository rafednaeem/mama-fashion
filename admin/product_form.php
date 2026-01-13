<?php
$pageTitle = 'Product';
require_once __DIR__ . '/admin_header.php';

$pdo = getPDO();
$categories = getCategories();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$editing = $id > 0;

$name = $description = $price = $image = '';
$salePrice = '';
$stock = 0;
$categoryId = $categories[0]['id'] ?? null;
$isActive = 1;
$isOnSale = 0;
$errors = [];

if ($editing) {
    $stmt = $pdo->prepare('SELECT * FROM products WHERE id = :id');
    $stmt->execute([':id' => $id]);
    $product = $stmt->fetch();
    if (!$product) {
        echo '<p class="section-subtitle">Product not found.</p>';
        require_once __DIR__ . '/admin_footer.php';
        exit;
    }
    $name = $product['name'];
    $description = $product['description'];
    $price = $product['price'];
    $salePrice = $product['sale_price'];
    $stock = $product['stock'];
    $categoryId = $product['category_id'];
    $isActive = $product['is_active'];
    $isOnSale = $product['is_on_sale'];
    $image = $product['image'];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['csrf_token'] ?? '';
    if (!verifyCsrfToken($token)) {
        $errors[] = 'Security check failed. Please try again.';
    }

    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $price = (float)($_POST['price'] ?? 0);
    $salePrice = trim($_POST['sale_price'] ?? '');
    $stock = (int)($_POST['stock'] ?? 0);
    $categoryId = (int)($_POST['category_id'] ?? 0);
    $isActive = isset($_POST['is_active']) ? 1 : 0;
    $isOnSale = isset($_POST['is_on_sale']) ? 1 : 0;

    if ($name === '') {
        $errors[] = 'Name is required.';
    }
    if ($price === '' || !is_numeric($price)) {
        $errors[] = 'Valid price is required.';
    }
    if ($salePrice !== '' && !is_numeric($salePrice)) {
        $errors[] = 'Sale price must be a number or left empty.';
    }
    if ($categoryId <= 0) {
        $errors[] = 'Please select a category.';
    }

    $uploadImageName = $image;
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = __DIR__ . '/../uploads/products/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        $tmp = $_FILES['image']['tmp_name'];
        $basename = basename($_FILES['image']['name']);
        $ext = strtolower(pathinfo($basename, PATHINFO_EXTENSION));
        if (!in_array($ext, ['jpg', 'jpeg', 'png', 'gif'])) {
            $errors[] = 'Product image must be an image (JPG, PNG, GIF).';
        } else {
            $safeName = 'prod_' . time() . '_' . preg_replace('/[^a-zA-Z0-9_\.-]/', '_', $basename);
            $dest = $uploadDir . $safeName;
            if (!move_uploaded_file($tmp, $dest)) {
                $errors[] = 'Failed to upload product image.';
            } else {
                $uploadImageName = $safeName;
            }
        }
    }

    if (empty($errors)) {
        $salePriceValue = ($salePrice === '') ? null : (float)$salePrice;

        if ($editing) {
            $wasOnSale = (int)$product['is_on_sale'];

            $stmt = $pdo->prepare('UPDATE products 
                                   SET name = :name, description = :description, price = :price, sale_price = :sale_price, is_on_sale = :is_on_sale, category_id = :category_id, is_active = :is_active, stock = :stock, image = :image 
                                   WHERE id = :id');
            $stmt->execute([
                ':name'        => $name,
                ':description' => $description,
                ':price'       => $price,
                ':sale_price'  => $salePriceValue,
                ':is_on_sale'  => $isOnSale,
                ':category_id' => $categoryId,
                ':is_active'   => $isActive,
                ':stock'       => $stock,
                ':image'       => $uploadImageName,
                ':id'          => $id,
            ]);

            if ($wasOnSale === 0 && $isOnSale === 1) {
                createSaleNotificationsForProduct($id);
            }
        } else {
            $stmt = $pdo->prepare('INSERT INTO products (name, description, price, sale_price, is_on_sale, category_id, is_active, stock, image) 
                                   VALUES (:name, :description, :price, :sale_price, :is_on_sale, :category_id, :is_active, :stock, :image)');
            $stmt->execute([
                ':name'        => $name,
                ':description' => $description,
                ':price'       => $price,
                ':sale_price'  => $salePriceValue,
                ':is_on_sale'  => $isOnSale,
                ':category_id' => $categoryId,
                ':is_active'   => $isActive,
                ':stock'       => $stock,
                ':image'       => $uploadImageName,
            ]);

            if ($isOnSale === 1) {
                $newId = (int)$pdo->lastInsertId();
                createSaleNotificationsForProduct($newId);
            }
        }
        redirect('/admin/products.php');
    }
}
?>

<section class="section">
    <div class="section-header">
        <div>
            <h1 class="section-title"><?php echo $editing ? 'Edit product' : 'Add product'; ?></h1>
            <p class="section-subtitle">Fill basic details and upload an image.</p>
        </div>
    </div>

    <div class="card" style="max-width:640px;">
        <?php if (!empty($errors)): ?>
            <div class="alert alert-error">
                <?php foreach ($errors as $err): ?>
                    <div><?php echo e($err); ?></div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <form method="post" enctype="multipart/form-data">
            <input type="hidden" name="csrf_token" value="<?php echo e(getCsrfToken()); ?>">
            <div class="form-group">
                <label for="name">Name</label>
                <input type="text" id="name" name="name" class="form-control" value="<?php echo e($name); ?>" required>
            </div>
            <div class="form-group">
                <label for="category_id">Category</label>
                <select id="category_id" name="category_id" class="form-control" required>
                    <option value="">Select...</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?php echo (int)$cat['id']; ?>" <?php echo $categoryId == $cat['id'] ? 'selected' : ''; ?>>
                            <?php echo e($cat['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="price">Price (PKR)</label>
                <input type="number" step="0.01" id="price" name="price" class="form-control" value="<?php echo e($price); ?>" required>
            </div>
            <div class="form-group">
                <label for="sale_price">Sale price (PKR, optional)</label>
                <input type="number" step="0.01" id="sale_price" name="sale_price" class="form-control" value="<?php echo e($salePrice); ?>">
                <p class="form-help">
                    Set a lower price and mark as “On sale” to notify customers who wishlisted this item.
                </p>
            </div>
            <div class="form-group">
                <label for="description">Description</label>
                <textarea id="description" name="description" class="form-control" rows="4"><?php echo e($description); ?></textarea>
            </div>
            <div class="form-group">
                <label for="stock">Stock Quantity</label>
                <input type="number" id="stock" name="stock" class="form-control" value="<?php echo e($stock); ?>" min="0" required>
                <p class="form-help">
                    Number of items available for sale. Set to 0 for out of stock.
                </p>
            </div>
            <div class="form-group">
                <label for="image">Product image</label>
                <input type="file" id="image" name="image" class="form-control" accept="image/*">
                <?php if ($image): ?>
                    <p class="form-help">
                        Current: <?php echo e($image); ?>
                    </p>
                <?php endif; ?>
            </div>
            <div class="form-group">
                <label>
                    <input type="checkbox" name="is_active" <?php echo $isActive ? 'checked' : ''; ?>>
                    Active (visible on site)
                </label>
            </div>
            <div class="form-group">
                <label>
                    <input type="checkbox" name="is_on_sale" <?php echo $isOnSale ? 'checked' : ''; ?>>
                    On sale (highlight on site &amp; notify wishlists)
                </label>
            </div>
            <button type="submit" class="btn btn-primary btn-small">
                <?php echo $editing ? 'Update product' : 'Create product'; ?>
            </button>
        </form>
    </div>
</section>

<?php
require_once __DIR__ . '/admin_footer.php';

