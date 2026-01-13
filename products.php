<?php
require_once __DIR__ . '/includes/header.php';

$pageTitle = 'Products';
$categories = getCategories();

$categoryId = isset($_GET['category']) ? (int)$_GET['category'] : null;
$search     = trim($_GET['q'] ?? '');
$minPrice   = (isset($_GET['min_price']) && $_GET['min_price'] !== '') ? (float)$_GET['min_price'] : null;
$maxPrice   = (isset($_GET['max_price']) && $_GET['max_price'] !== '') ? (float)$_GET['max_price'] : null;
$onlyOnSale = isset($_GET['on_sale']) && $_GET['on_sale'] === '1';

$products = getProducts(
    $categoryId ?: null,
    0,
    $search !== '' ? $search : null,
    $minPrice,
    $maxPrice,
    $onlyOnSale ? true : null
);
?>

<section class="section">
    <div class="container">
        <div class="section-header">
            <div>
                <h1 class="section-title">All products</h1>
                <p class="section-subtitle">
                    Browse stitched &amp; unstitched suits, jewelry and accessories.
                </p>
            </div>
        </div>

        <form method="get" style="margin-bottom:12px;">
            <div class="form-row">
                <div class="form-group">
                    <label for="q">Search</label>
                    <input type="text" id="q" name="q" class="form-control" placeholder="Suit, kurti, jewelry..." value="<?php echo e($search); ?>">
                </div>
                <div class="form-group">
                    <label for="category">Category</label>
                    <select id="category" name="category" class="form-control">
                        <option value="">All</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?php echo (int)$cat['id']; ?>" <?php echo $categoryId == $cat['id'] ? 'selected' : ''; ?>>
                                <?php echo e($cat['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label for="min_price">Min price (PKR)</label>
                    <input type="number" id="min_price" name="min_price" class="form-control" value="<?php echo $minPrice !== null ? e((string)$minPrice) : ''; ?>">
                </div>
                <div class="form-group">
                    <label for="max_price">Max price (PKR)</label>
                    <input type="number" id="max_price" name="max_price" class="form-control" value="<?php echo $maxPrice !== null ? e((string)$maxPrice) : ''; ?>">
                </div>
                <div class="form-group">
                    <label>&nbsp;</label>
                    <div>
                        <label>
                            <input type="checkbox" name="on_sale" value="1" <?php echo $onlyOnSale ? 'checked' : ''; ?>>
                            On sale only
                        </label>
                    </div>
                </div>
            </div>
            <button type="submit" class="btn btn-primary btn-small">Apply filters</button>
        </form>

        <?php if (empty($products)): ?>
            <p class="section-subtitle">No products available in this category yet.</p>
        <?php else: ?>
            <div class="product-grid">
                <?php foreach ($products as $product): ?>
                    <article class="product-card">
                        <a href="<?php echo $baseUrl; ?>/product_detail.php?id=<?php echo (int)$product['id']; ?>">
                            <div class="product-image">
                                <?php if (!empty($product['image'])): ?>
                                    <img src="<?php echo $baseUrl . '/uploads/products/' . e($product['image']); ?>" alt="<?php echo e($product['name']); ?>">
                                <?php endif; ?>
                            </div>
                        </a>
                        <div class="product-body">
                            <p class="product-category"><?php echo e($product['category_name']); ?></p>
                            <h3 class="product-name"><?php echo e($product['name']); ?></h3>
                            <div class="product-price-row">
                                <?php if (!empty($product['is_on_sale']) && $product['sale_price'] !== null): ?>
                                    <span class="product-price">
                                        <span style="text-decoration:line-through; opacity:0.7; margin-right:6px;">
                                            Rs <?php echo number_format($product['price']); ?>
                                        </span>
                                        <span style="color:#b91c1c; font-weight:600;">
                                            Rs <?php echo number_format($product['sale_price']); ?>
                                        </span>
                                    </span>
                                <?php else: ?>
                                    <span class="product-price">Rs <?php echo number_format($product['price']); ?></span>
                                <?php endif; ?>
                                
                                <?php if (isset($product['stock']) && $product['stock'] > 0): ?>
                                    <a href="<?php echo $baseUrl; ?>/add_to_cart.php?id=<?php echo (int)$product['id']; ?>&source=products" class="btn btn-primary btn-small">
                                        Add to cart
                                    </a>
                                <?php else: ?>
                                    <button class="btn btn-disabled btn-small" disabled>
                                        Out of Stock
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php
require_once __DIR__ . '/includes/footer.php';

