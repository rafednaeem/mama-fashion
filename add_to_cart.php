<?php
require_once __DIR__ . '/includes/functions.php';

// Accept both GET (from quick button) and POST (from detail page).
$productId = 0;
$quantity  = 1;
$error = '';
$success = '';
$source = '';

if (isset($_POST['product_id'])) {
    $productId = (int)$_POST['product_id'];
    $quantity  = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;
    $source    = $_POST['source'] ?? 'products';
} elseif (isset($_GET['id'])) {
    $productId = (int)$_GET['id'];
    $source    = $_GET['source'] ?? 'products';
}

if ($productId > 0) {
    try {
        addToCart($productId, $quantity);
        $success = 'Product added to cart successfully!';
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

// Store message in session and redirect back
if ($success) {
    $_SESSION['cart_success'] = $success;
} elseif ($error) {
    $_SESSION['cart_error'] = $error;
}

// Redirect based on source
if ($source === 'product_detail' && $productId > 0) {
    redirect("/product_detail.php?id=$productId");
} elseif ($source === 'home') {
    redirect('/index.php');
} else {
    redirect('/products.php');
}

