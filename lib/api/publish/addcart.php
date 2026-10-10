<?php
session_start();
require_once('../../db.php');
$variant_id = (int) ($_POST['variant_id'] ?? 0);
$product_id = (int) ($_POST['product_id'] ?? 0);
$quantity = (int) ($_POST['quantity'] ?? 0);
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    header('Location: ../../../signin.php');

    exit('Необходимо авторизоваться');
}
if (
    empty($_POST['csrf_token']) ||
    empty($_SESSION['csrf_token']) ||
    !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])
) {
    http_response_code(403);
    exit('Неверный CSRF-токен');
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
    if ($e->errorInfo[0] === '23000' && $e->errorInfo[1] == 1062) {
    $_SESSION['error'] = 'Этот товар уже есть в корзине.';
        header("Location:  ../../../product.php?id={$product_id}");
    } else {
        throw $e;
    }
}


