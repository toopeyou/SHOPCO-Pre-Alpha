<?php
session_start();

if (
    empty($_POST['csrf_token']) ||
    empty($_SESSION['csrf_token']) ||
    !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])
) {
    http_response_code(403);
    exit('Неверный CSRF-токен');
}
require_once('../../db.php');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    header('Location: ../../../signin.php');
    exit('Необходимо авторизоваться');
}
$userId = (int) $_SESSION['user_id'];
$variant_id = (int) ($_POST['variant_id'] ?? 0);
try {
    $sql = "DELETE FROM cart_items WHERE user_id = ? AND variant_id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        $userId,
        $variant_id
    ]);
    header('Location: ../../../cart.php');
    exit;
} catch (Exception $e) {
    echo $e;
}


