<?php
require_once __DIR__ . '/includes/header.php';

// Determine product ID from GET or POST (for review submissions).
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['review_submit'])) {
    $idFromPost = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
    if ($idFromPost > 0) {
        $id = $idFromPost;
    }
}

$product = $id > 0 ? getProductById($id) : null;

if (!$product) {
    echo '<div class="container"><p class="section-subtitle" style="margin-top:24px;">Product not found.</p></div>';
    require_once __DIR__ . '/includes/footer.php';
    exit;
}

$pageTitle = $product['name'];

$errors = [];
$reviewSuccess = '';

// Handle new review submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['review_submit'])) {
    if (!isUserLoggedIn()) {
        $errors[] = 'Please log in to leave a review.';
    } else {
        $token = $_POST['csrf_token'] ?? '';
        if (!verifyCsrfToken($token)) {
            $errors[] = 'Security check failed. Please try again.';
        } else {
            $rating  = (int)($_POST['rating'] ?? 0);
            $comment = trim($_POST['comment'] ?? '');

            if ($rating < 1 || $rating > 5) {
                $errors[] = 'Please select a rating between 1 and 5 stars.';
            }
            if ($comment === '') {
                $errors[] = 'Please add a short comment about the product.';
            }

            if (empty($errors)) {
                addProductReview($id, (int)$_SESSION['user_id'], $rating, $comment);
                $reviewSuccess = 'Thank you! Your review has been added.';
            }
        }
    }
}

$avgRating = getProductAverageRating($product['id']);
$reviews   = getProductReviews($product['id']);

function render_stars(float $rating): string
{
    $full = (int)floor($rating);
    $out = '';
    for ($i = 1; $i <= 5; $i++) {
        $out .= $i <= $full ? '★' : '☆';
    }
    return $out;
}
?>

<section class="section">
    <div class="container">
        <div class="section-header">
            <div>
                <h1 class="section-title"><?php echo e($product['name']); ?></h1>
                <p class="section-subtitle">
                    <?php echo e($product['category_name']); ?>
                </p>
            </div>
        </div>

        <div class="cart-layout">
            <div class="card">
                <div class="product-image" style="margin-bottom:10px;">
                    <?php if (!empty($product['image'])): ?>
                        <img src="<?php echo $baseUrl . '/uploads/products/' . e($product['image']); ?>" alt="<?php echo e($product['name']); ?>">
                    <?php endif; ?>
                </div>
                <p class="section-subtitle">
                    <?php echo nl2br(e($product['description'])); ?>
                </p>
                <?php if ($avgRating !== null): ?>
                    <div class="rating-summary">
                        <span class="rating-stars"><?php echo render_stars($avgRating); ?></span>
                        <span class="rating-score"><?php echo number_format($avgRating, 1); ?>/5</span>
                        <span class="rating-count">(<?php echo count($reviews); ?> review<?php echo count($reviews) === 1 ? '' : 's'; ?>)</span>
                    </div>
                <?php else: ?>
                    <p class="form-help" style="margin-top:6px;">
                        No reviews yet. Be the first to share your experience.
                    </p>
                <?php endif; ?>
            </div>

            <div class="card">
                <h2 class="card-title">Order details</h2>
                <p class="section-subtitle" style="margin-bottom: 8px;">
                    <?php if (!empty($product['is_on_sale']) && $product['sale_price'] !== null): ?>
                        <span style="display:block;">
                            <span style="text-decoration:line-through; opacity:0.7; margin-right:6px;">
                                Rs <?php echo number_format($product['price']); ?>
                            </span>
                            <strong style="color:#b91c1c;">Rs <?php echo number_format($product['sale_price']); ?></strong>
                        </span>
                    <?php else: ?>
                        Price: <strong>Rs <?php echo number_format($product['price']); ?></strong>
                    <?php endif; ?>
                </p>
                <p class="form-help">
                    <?php if (isset($product['stock'])): ?>
                        <?php if ($product['stock'] > 0): ?>
                            <span style="color: #28a745; font-weight: 600;">
                                ✓ In Stock (<?php echo $product['stock']; ?> available)
                            </span>
                        <?php else: ?>
                            <span style="color: #dc3545; font-weight: 600;">
                                ✗ Out of Stock
                            </span>
                        <?php endif; ?>
                    <?php endif; ?>
                </p>
                <?php if (!isset($product['stock']) || $product['stock'] > 0): ?>
                    <form action="<?php echo $baseUrl; ?>/add_to_cart.php" method="post">
                        <input type="hidden" name="product_id" value="<?php echo (int)$product['id']; ?>">
                        <input type="hidden" name="source" value="product_detail">
                        <div class="form-group">
                            <label for="qty">Quantity</label>
                            <input type="number" class="form-control" id="qty" name="quantity" min="1" value="1" 
                                   <?php echo isset($product['stock']) ? 'max="' . $product['stock'] . '"' : ''; ?>>
                        </div>
                        <button type="submit" class="btn btn-primary btn-small">
                            Add to cart
                        </button>
                    </form>
                <?php else: ?>
                    <button class="btn btn-disabled btn-small" disabled>
                        Out of Stock
                    </button>
                <?php endif; ?>
                <?php if (isUserLoggedIn()): ?>
                    <p class="form-help" style="margin-top:8px;">
                        <a href="<?php echo $baseUrl; ?>/add_to_wishlist.php?id=<?php echo (int)$product['id']; ?>">
                            ♥ Add to wishlist
                        </a>
                    </p>
                <?php endif; ?>
                <p class="form-help" style="margin-top:8px;">
                    Cash on Delivery and bank transfer available across Pakistan.
                </p>
            </div>
        </div>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="section-header">
            <div>
                <h2 class="section-title">Customer reviews</h2>
                <p class="section-subtitle">
                    Read what other customers say about this item.
                </p>
            </div>
        </div>

        <div class="cart-layout">
            <div class="card">
                <?php if (!empty($reviews)): ?>
                    <?php foreach ($reviews as $review): ?>
                        <div class="review-item">
                            <div class="review-header">
                                <span class="review-author"><?php echo e($review['name']); ?></span>
                                <span class="rating-stars">
                                    <?php echo render_stars((float)$review['rating']); ?>
                                </span>
                            </div>
                            <p class="review-comment">
                                <?php echo nl2br(e($review['comment'])); ?>
                            </p>
                            <p class="review-date">
                                <?php echo e(date('d M Y', strtotime($review['created_at']))); ?>
                            </p>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="section-subtitle">No reviews yet for this product.</p>
                <?php endif; ?>
            </div>

            <div class="card">
                <h3 class="card-title">Write a review</h3>

                <?php if (!empty($errors)): ?>
                    <div class="alert alert-error">
                        <?php foreach ($errors as $err): ?>
                            <div><?php echo e($err); ?></div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <?php if ($reviewSuccess): ?>
                    <div class="alert alert-success">
                        <?php echo e($reviewSuccess); ?>
                    </div>
                <?php endif; ?>

                <?php if (!isUserLoggedIn()): ?>
                    <p class="form-help">
                        Please <a href="<?php echo $baseUrl; ?>/login.php">log in</a> to leave a review.
                    </p>
                <?php else: ?>
                    <form method="post">
                        <input type="hidden" name="product_id" value="<?php echo (int)$product['id']; ?>">
                        <input type="hidden" name="csrf_token" value="<?php echo e(getCsrfToken()); ?>">
                        <div class="form-group">
                            <label for="rating">Rating</label>
                            <select id="rating" name="rating" class="form-control" required>
                                <option value="">Select rating</option>
                                <?php for ($i = 5; $i >= 1; $i--): ?>
                                    <option value="<?php echo $i; ?>"><?php echo $i; ?> star<?php echo $i > 1 ? 's' : ''; ?></option>
                                <?php endfor; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="comment">Your review</label>
                            <textarea id="comment" name="comment" class="form-control" rows="4" required></textarea>
                        </div>
                        <button type="submit" name="review_submit" value="1" class="btn btn-primary btn-small">
                            Submit review
                        </button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<?php
require_once __DIR__ . '/includes/footer.php';

