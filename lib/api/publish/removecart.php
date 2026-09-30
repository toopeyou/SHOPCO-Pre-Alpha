<?php
session_start();

require_once('../../db.php');

$variant_id = (int) ($_POST['variant_id'] ?? 0);
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    exit('Необходимо авторизоваться');
}

$userId = (int) $_SESSION['user_id'];

try {
    $sql = "DELETE FROM cart_items WHERE user_id = ? AND variant_id = ?";
    ;
    $query = $pdo->prepare($sql);
    $query->execute([
        $userId,
        $variant_id
    ]);
    header('Location: ../../../cart.php');

    exit;

} catch (Exception $e) {
    echo $e;
}


