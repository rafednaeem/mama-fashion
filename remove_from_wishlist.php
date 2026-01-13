<?php
require_once __DIR__ . '/includes/functions.php';

if (!isUserLoggedIn()) {
    redirect('/login.php');
}

$productId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($productId > 0) {
    removeFromWishlist((int)$_SESSION['user_id'], $productId);
}

redirect('/my_wishlist.php');

