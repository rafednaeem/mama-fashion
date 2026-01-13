<?php
// admin/admin_header.php
require_once __DIR__ . '/../includes/functions.php';

if (!isAdminLoggedIn() && basename($_SERVER['PHP_SELF']) !== 'login.php') {
    redirect('/admin/login.php');
}

$pageTitle = $pageTitle ?? 'Admin';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo e($pageTitle); ?> | MAMA Admin</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@500;600;700&family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo $baseUrl; ?>/assets/css/style.css">
    <style>
        .admin-shell {
            padding-top: 64px;
        }
        .admin-topbar {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 30;
            background: #111827;
            color: #e5e7eb;
            padding: 8px 0;
            box-shadow: 0 10px 24px rgba(15, 23, 42, 0.8);
        }
        .admin-topbar-inner {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
        }
        .admin-brand {
            font-family: "Playfair Display", "Times New Roman", serif;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            font-size: 0.9rem;
        }
        .admin-nav {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 0.78rem;
        }
        .admin-nav a {
            color: #e5e7eb;
            text-decoration: none;
            padding: 3px 8px;
            border-radius: 999px;
            border: 1px solid transparent;
        }
        .admin-nav a:hover {
            border-color: rgba(248, 250, 252, 0.3);
        }
        .admin-main {
            padding: 18px 0 26px;
        }
    </style>
</head>
<body>
    <header class="admin-topbar">
        <div class="container admin-topbar-inner">
            <div class="admin-brand">
                MAMA <span style="opacity:0.7;">Admin</span>
            </div>
            <?php if (isAdminLoggedIn()): ?>
                <nav class="admin-nav">
                    <a href="<?php echo $baseUrl; ?>/admin/dashboard.php">Dashboard</a>
                    <a href="<?php echo $baseUrl; ?>/admin/products.php">Products</a>
                    <a href="<?php echo $baseUrl; ?>/admin/inventory.php">Inventory</a>
                    <a href="<?php echo $baseUrl; ?>/admin/orders.php">Orders</a>
                    <span>Hi, <?php echo e($_SESSION['admin_name'] ?? 'Admin'); ?></span>
                    <a href="<?php echo $baseUrl; ?>/admin/logout.php">Logout</a>
                </nav>
            <?php endif; ?>
        </div>
    </header>
    <main class="admin-shell">
        <div class="container admin-main">

