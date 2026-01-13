<?php
require_once __DIR__ . '/includes/functions.php';

if (!isUserLoggedIn()) {
    // After login, user can navigate back manually.
    redirect('/login.php');
}

$productId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($productId > 0) {
    addToWishlist((int)$_SESSION['user_id'], $productId);
}

// Redirect back to product listing by default.
redirect('/my_wishlist.php');

