<?php
// includes/functions.php
// Helper functions used across the application.

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

/**
 * Get all categories.
 */
function getCategories(): array
{
    $pdo = getPDO();
    $stmt = $pdo->query('SELECT id, name, slug FROM categories ORDER BY name');
    return $stmt->fetchAll();
}

/**
 * Get products for home/listing, with optional filters.
 */
function getProducts(
    ?int $categoryId = null,
    int $limit = 0,
    ?string $search = null,
    ?float $minPrice = null,
    ?float $maxPrice = null,
    ?bool $onlyOnSale = null
): array {
    $pdo = getPDO();
    $sql = 'SELECT p.*, c.name AS category_name 
            FROM products p 
            JOIN categories c ON p.category_id = c.id 
            WHERE p.is_active = 1';

    $params = [];

    if ($categoryId !== null) {
        $sql .= ' AND p.category_id = :cid';
        $params[':cid'] = $categoryId;
    }
    if ($search !== null && $search !== '') {
        $sql .= ' AND (p.name LIKE :q OR p.description LIKE :q)';
        $params[':q'] = '%' . $search . '%';
    }
    if ($minPrice !== null) {
        $sql .= ' AND (COALESCE(p.sale_price, p.price) >= :minPrice)';
        $params[':minPrice'] = $minPrice;
    }
    if ($maxPrice !== null) {
        $sql .= ' AND (COALESCE(p.sale_price, p.price) <= :maxPrice)';
        $params[':maxPrice'] = $maxPrice;
    }
    if ($onlyOnSale === true) {
        $sql .= ' AND p.is_on_sale = 1';
    }

    $sql .= ' ORDER BY p.created_at DESC';

    if ($limit > 0) {
        $sql .= ' LIMIT ' . (int)$limit;
    }

    $stmt = $pdo->prepare($sql);
    foreach ($params as $key => $value) {
        if (in_array($key, [':cid', ':minPrice', ':maxPrice']) && is_numeric($value)) {
            $stmt->bindValue($key, (float)$value);
        } else {
            $stmt->bindValue($key, $value);
        }
    }
    $stmt->execute();
    return $stmt->fetchAll();
}

/**
 * Get single product by ID.
 */
function getProductById(int $id): ?array
{
    $pdo = getPDO();
    $stmt = $pdo->prepare('SELECT p.*, c.name AS category_name 
                           FROM products p 
                           JOIN categories c ON p.category_id = c.id 
                           WHERE p.id = :id AND p.is_active = 1');
    $stmt->execute([':id' => $id]);
    $product = $stmt->fetch();
    return $product ?: null;
}

/**
 * Simple helper to escape HTML.
 */
function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

/**
 * Check if user is logged in (customer).
 */
function isUserLoggedIn(): bool
{
    return !empty($_SESSION['user_id']);
}

/**
 * Check if admin is logged in.
 */
function isAdminLoggedIn(): bool
{
    return !empty($_SESSION['admin_id']);
}

/**
 * CSRF token helpers
 */
function getCsrfToken(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verifyCsrfToken(string $token): bool
{
    if (empty($_SESSION['csrf_token']) || $token === '') {
        return false;
    }
    return hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Redirect helper.
 */
function redirect(string $path): void
{
    global $baseUrl;
    header('Location: ' . $baseUrl . $path);
    exit;
}

/**
 * Get cart items from session.
 */
function getCartItems(): array
{
    return $_SESSION['cart'] ?? [];
}

/**
 * Add a product to cart.
 */
function addToCart(int $productId, int $qty = 1): void
{
    if ($qty < 1) {
        $qty = 1;
    }

    // Check product stock
    $pdo = getPDO();
    $stmt = $pdo->prepare('SELECT stock FROM products WHERE id = :id AND is_active = 1');
    $stmt->execute([':id' => $productId]);
    $product = $stmt->fetch();
    
    if (!$product) {
        throw new Exception('Product not found or inactive');
    }
    
    if ($product['stock'] <= 0) {
        throw new Exception('Product is out of stock');
    }

    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }

    $currentQty = isset($_SESSION['cart'][$productId]) ? $_SESSION['cart'][$productId]['qty'] : 0;
    $totalQty = $currentQty + $qty;
    
    if ($totalQty > $product['stock']) {
        throw new Exception('Not enough stock available. Only ' . $product['stock'] . ' items in stock.');
    }

    if (isset($_SESSION['cart'][$productId])) {
        $_SESSION['cart'][$productId]['qty'] += $qty;
    } else {
        $_SESSION['cart'][$productId] = [
            'product_id' => $productId,
            'qty'        => $qty,
        ];
    }
}

/**
 * Update cart quantity.
 */
function updateCartItem(int $productId, int $qty): void
{
    if (!isset($_SESSION['cart'][$productId])) {
        return;
    }

    if ($qty <= 0) {
        unset($_SESSION['cart'][$productId]);
    } else {
        // Check product stock
        $pdo = getPDO();
        $stmt = $pdo->prepare('SELECT stock FROM products WHERE id = :id AND is_active = 1');
        $stmt->execute([':id' => $productId]);
        $product = $stmt->fetch();
        
        if (!$product) {
            unset($_SESSION['cart'][$productId]);
            return;
        }
        
        if ($qty > $product['stock']) {
            throw new Exception('Not enough stock available. Only ' . $product['stock'] . ' items in stock.');
        }
        
        $_SESSION['cart'][$productId]['qty'] = $qty;
    }
}

/**
 * Clear cart.
 */
function clearCart(): void
{
    unset($_SESSION['cart']);
}

/**
 * Update product stock after order completion
 */
function updateProductStock(int $productId, int $quantity): void
{
    $pdo = getPDO();
    $stmt = $pdo->prepare('UPDATE products SET stock = stock - :quantity WHERE id = :id AND stock >= :quantity');
    $stmt->execute([':quantity' => $quantity, ':id' => $productId]);
}

/**
 * Update stock for multiple products (used in order processing)
 */
function updateStockForOrder(array $items): void
{
    $pdo = getPDO();
    $pdo->beginTransaction();
    
    try {
        foreach ($items as $item) {
            updateProductStock($item['product_id'], $item['quantity']);
        }
        $pdo->commit();
    } catch (Exception $e) {
        $pdo->rollback();
        throw $e;
    }
}

/**
 * Wishlist helpers
 */
function addToWishlist(int $userId, int $productId): void
{
    $pdo = getPDO();
    $stmt = $pdo->prepare('INSERT IGNORE INTO wishlists (user_id, product_id) VALUES (:user_id, :product_id)');
    $stmt->execute([
        ':user_id'    => $userId,
        ':product_id' => $productId,
    ]);
}

function removeFromWishlist(int $userId, int $productId): void
{
    $pdo = getPDO();
    $stmt = $pdo->prepare('DELETE FROM wishlists WHERE user_id = :user_id AND product_id = :product_id');
    $stmt->execute([
        ':user_id'    => $userId,
        ':product_id' => $productId,
    ]);
}

function getWishlistItems(int $userId): array
{
    $pdo = getPDO();
    $stmt = $pdo->prepare('SELECT w.*, p.name, p.image, p.price, p.sale_price, p.is_on_sale, c.name AS category_name
                           FROM wishlists w
                           JOIN products p ON w.product_id = p.id
                           JOIN categories c ON p.category_id = c.id
                           WHERE w.user_id = :user_id
                           ORDER BY w.created_at DESC');
    $stmt->execute([':user_id' => $userId]);
    return $stmt->fetchAll();
}

function getWishlistCount(int $userId): int
{
    $pdo = getPDO();
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM wishlists WHERE user_id = :user_id');
    $stmt->execute([':user_id' => $userId]);
    return (int)$stmt->fetchColumn();
}

/**
 * Reviews helpers
 */
function getProductReviews(int $productId): array
{
    $pdo = getPDO();
    $stmt = $pdo->prepare('SELECT r.*, u.name 
                           FROM product_reviews r 
                           JOIN users u ON r.user_id = u.id 
                           WHERE r.product_id = :pid 
                           ORDER BY r.created_at DESC');
    $stmt->execute([':pid' => $productId]);
    return $stmt->fetchAll();
}

function addProductReview(int $productId, int $userId, int $rating, string $comment): void
{
    $rating = max(1, min(5, $rating));
    $pdo = getPDO();
    $stmt = $pdo->prepare('INSERT INTO product_reviews (product_id, user_id, rating, comment) 
                           VALUES (:product_id, :user_id, :rating, :comment)');
    $stmt->execute([
        ':product_id' => $productId,
        ':user_id'    => $userId,
        ':rating'     => $rating,
        ':comment'    => $comment,
    ]);
}

function getProductAverageRating(int $productId): ?float
{
    $pdo = getPDO();
    $stmt = $pdo->prepare('SELECT AVG(rating) AS avg_rating FROM product_reviews WHERE product_id = :pid');
    $stmt->execute([':pid' => $productId]);
    $row = $stmt->fetch();
    return $row && $row['avg_rating'] !== null ? (float)$row['avg_rating'] : null;
}

/**
 * Notifications
 */
function createSaleNotificationsForProduct(int $productId): void
{
    $pdo = getPDO();
    $stmt = $pdo->prepare('SELECT DISTINCT user_id FROM wishlists WHERE product_id = :pid');
    $stmt->execute([':pid' => $productId]);
    $userIds = $stmt->fetchAll(PDO::FETCH_COLUMN);

    if (!$userIds) {
        return;
    }

    $product = getProductById($productId);
    if (!$product) {
        return;
    }

    $priceForMsg = $product['sale_price'] !== null ? $product['sale_price'] : $product['price'];
    $message = sprintf(
        'Good news! "%s" is now on sale for Rs %s.',
        $product['name'],
        number_format($priceForMsg)
    );

    $insert = $pdo->prepare('INSERT INTO notifications (user_id, product_id, type, message) 
                             VALUES (:user_id, :product_id, :type, :message)');
    foreach ($userIds as $uid) {
        $insert->execute([
            ':user_id'    => $uid,
            ':product_id' => $productId,
            ':type'       => 'sale',
            ':message'    => $message,
        ]);
    }
}

function getUnreadNotificationCount(int $userId): int
{
    $pdo = getPDO();
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM notifications WHERE user_id = :user_id AND is_read = 0');
    $stmt->execute([':user_id' => $userId]);
    return (int)$stmt->fetchColumn();
}

function getNotifications(int $userId): array
{
    $pdo = getPDO();
    $stmt = $pdo->prepare('SELECT n.*, p.name AS product_name 
                           FROM notifications n 
                           LEFT JOIN products p ON n.product_id = p.id
                           WHERE n.user_id = :user_id
                           ORDER BY n.created_at DESC');
    $stmt->execute([':user_id' => $userId]);
    return $stmt->fetchAll();
}

function markAllNotificationsRead(int $userId): void
{
    $pdo = getPDO();
    $stmt = $pdo->prepare('UPDATE notifications SET is_read = 1 WHERE user_id = :user_id');
    $stmt->execute([':user_id' => $userId]);
}
