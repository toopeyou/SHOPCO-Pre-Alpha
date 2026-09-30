<?php
session_start();
require_once('../../db.php');
$variant_id = (int) ($_POST['variant_id'] ?? 0);
$product_id = (int) ($_POST['product_id'] ?? 0);
$quantity = (int) ($_POST['quantity'] ?? 0);
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    exit('Необходимо авторизоваться');
}

$userId = (int) $_SESSION['user_id'];


try {
    $sql = '
        INSERT INTO cart_items (user_id, variant_id, quantity)
        VALUES (?, ?, ?)
        ';
    $query = $pdo->prepare($sql);
    $query->execute([
        $userId,
        $variant_id,
        $quantity
    ]);

    header("Location:  ../../../product.php?id={$product_id}");


    exit;

} catch (Exception $e) {
    echo $e;
}


