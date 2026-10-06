<?php
session_start();
require_once('../../db.php');
$product_id = (int) ($_POST['product_id'] ?? 0);
$action = ($_POST['action'] ?? 0);
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    exit('Необходимо авторизоваться');
}
$userId = (int) $_SESSION['user_id'];

if ($action === 'add') {
    try {
        $sql = " INSERT INTO wishlist (user_id, product_id) VALUES (?, ?)";

        $query = $pdo->prepare($sql);
        $query->execute([
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
    } catch (Exception $e) {
    
    }
}



