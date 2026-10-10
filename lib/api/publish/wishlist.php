<?php
session_start();
require_once('../../db.php');
$product_id = (int) ($_POST['product_id'] ?? 0);
$action = ($_POST['action'] ?? 0);
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
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
if ($action === 'add') {
    try {
        $sql = " INSERT INTO wishlist (user_id, product_id) VALUES (?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $userId,
            $product_id
        ]);
        header("Location:  ../../../product.php?id={$product_id}");
        exit;
    }catch (Exception $e) {
        echo $e;
    }
}

if ($_POST['action'] === 'remove') {
    try{   
    $stmt = $pdo->prepare("
        DELETE FROM wishlist
        WHERE user_id = :user_id
          AND product_id = :product_id
    ");

    $stmt->execute([
        ':user_id' => $userId,
        ':product_id' => $product_id
    ]);
    header("Location:  ../../../product.php?id={$product_id}");
    exit;
    } catch (Exception $e) {}
}



