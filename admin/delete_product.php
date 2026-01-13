<?php
require_once __DIR__ . '/admin_header.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id > 0) {
    $pdo = getPDO();
    $stmt = $pdo->prepare('DELETE FROM products WHERE id = :id');
    $stmt->execute([':id' => $id]);
}

redirect('/admin/products.php');

