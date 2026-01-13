<?php
require_once __DIR__ . '/includes/functions.php';

$error = '';

if (isset($_POST['qty']) && is_array($_POST['qty'])) {
    foreach ($_POST['qty'] as $productId => $qty) {
        $productId = (int)$productId;
        $qty = (int)$qty;
        try {
            updateCartItem($productId, $qty);
        } catch (Exception $e) {
            $error = $e->getMessage();
            break;
        }
    }
}

// Store message in session
if ($error) {
    $_SESSION['cart_error'] = $error;
}

redirect('/cart.php');

