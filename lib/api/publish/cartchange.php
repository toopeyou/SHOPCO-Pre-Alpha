<?php
session_start();
require_once('../../db.php');
$quantity = (int) ($_POST['quantity'] ?? 0);
$variant_id = (int) ($_POST['variant_id'] ?? 0);

$action = ($_POST['action'] ?? 0);


if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    exit('Необходимо авторизоваться');
}

$userId = (int) $_SESSION['user_id'];



$stmt = $pdo->prepare( "SELECT stock FROM product_variants WHERE id = ?");
$stmt->execute([$variant_id]);
$quantityProduct = $stmt->fetch(PDO::FETCH_ASSOC);

$stmt = $pdo->prepare( "SELECT quantity FROM cart_items WHERE user_id = ? AND variant_id = ?");
$stmt->execute([$userId, $variant_id]);
$quantityCart = $stmt->fetch(PDO::FETCH_ASSOC);



if ($action === 'add') {
    try {
        if ($quantityCart['quantity'] >= $quantityProduct['stock']) {
            header("Location:  ../../../cart.php");
            exit;
        }else {
            $stmt = $pdo->prepare(
            "UPDATE cart_items 
            SET quantity = quantity + 1 
            WHERE user_id = ? AND variant_id = ?"
        );

        $stmt->execute([
                $userId,
                $variant_id
            ]);

            header("Location:  ../../../cart.php");

        }
    } catch (\Throwable $th) {

    }
}


if ($action === 'remove') {
    try {
        if ($quantityCart['quantity'] <= 1) {
            header("Location:  ../../../cart.php");
            exit;
        }else {
            $stmt = $pdo->prepare(
            "UPDATE cart_items 
            SET quantity = quantity - 1
            WHERE user_id = ? AND variant_id = ?"
        );

        $stmt->execute([
                $userId,
                $variant_id
            ]);

            header("Location:  ../../../cart.php");

        }
    } catch (\Throwable $th) {

    }
}






