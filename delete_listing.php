<?php
require_once __DIR__ . '/includes/auth.php';
requireLogin();
require_once __DIR__ . '/includes/db.php';

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id) { header("Location: " . BASE_URL . "/sell.php"); exit; }

// Only the seller or admin can delete
$stmt = $pdo->prepare("SELECT seller_id FROM products WHERE id = ?");
$stmt->execute([$id]);
$product = $stmt->fetch();

if (!$product || ($product['seller_id'] != $_SESSION['user_id'] && !isAdmin())) {
    header("Location: " . BASE_URL . "/sell.php");
    exit;
}

$pdo->prepare("DELETE FROM products WHERE id = ?")->execute([$id]);
header("Location: " . BASE_URL . "/sell.php?deleted=1");
exit;
