<?php
// includes/header.php
require_once __DIR__ . '/functions.php';

// Simple page title handling.
$pageTitle = $pageTitle ?? 'MAMA Fashion';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo e($pageTitle); ?> | MAMA Fashion</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@500;600;700&family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo $baseUrl; ?>/assets/css/style.css">
</head>
<body>
<?php include __DIR__ . '/navbar.php'; ?>

<?php
// Display success and error messages
if (isset($_SESSION['cart_success'])) {
    echo '<div class="alert alert-success" style="margin: 20px auto; max-width: 800px;">' . e($_SESSION['cart_success']) . '</div>';
    unset($_SESSION['cart_success']);
}
if (isset($_SESSION['cart_error'])) {
    echo '<div class="alert alert-error" style="margin: 20px auto; max-width: 800px;">' . e($_SESSION['cart_error']) . '</div>';
    unset($_SESSION['cart_error']);
}
?>

<main class="page-wrapper">

