<?php
require_once __DIR__ . '/includes/header.php';

if (!isUserLoggedIn()) {
    redirect('/login.php');
}

$pageTitle = 'Notifications';
$userId = (int)$_SESSION['user_id'];

// Mark all as read when visiting this page.
markAllNotificationsRead($userId);
$notifications = getNotifications($userId);
?>

<section class="section">
    <div class="container">
        <div class="section-header">
            <div>
                <h1 class="section-title">Alerts &amp; updates</h1>
                <p class="section-subtitle">
                    Price drops on wishlist items and important order updates appear here.
                </p>
            </div>
        </div>

        <div class="card">
            <?php if (empty($notifications)): ?>
                <p class="section-subtitle">No notifications yet.</p>
            <?php else: ?>
                <?php foreach ($notifications as $note): ?>
                    <div class="review-item">
                        <div class="review-header">
                            <span class="review-author">
                                <?php echo e($note['type'] === 'sale' ? 'Sale alert' : 'Update'); ?>
                            </span>
                            <span class="review-date">
                                <?php echo e(date('d M Y H:i', strtotime($note['created_at']))); ?>
                            </span>
                        </div>
                        <p class="review-comment">
                            <?php echo e($note['message']); ?>
                            <?php if (!empty($note['product_id'])): ?>
                                <a href="<?php echo $baseUrl; ?>/product_detail.php?id=<?php echo (int)$note['product_id']; ?>">
                                    View product
                                </a>
                            <?php endif; ?>
                        </p>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</section>

<?php
require_once __DIR__ . '/includes/footer.php';

