<?php
declare(strict_types=1);

session_start();
require_once('../../db.php');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    exit('Необходимо авторизоваться');
}

$userId = (int) $_SESSION['user_id'];
$sql = 'SELECT role FROM users WHERE id = ? LIMIT 1';
$stmt = $pdo->prepare($sql);
$stmt->execute([$userId]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user || $user['role'] !== 'admin') {
    http_response_code(403);
    exit('Доступ запрещён');
}
$review_id = (int) ($_POST['review_id'] ?? 0);
$product_id = (int) ($_POST['product_id'] ?? 0);

try {

    $stmt = $pdo->prepare("DELETE FROM reviews WHERE id = :id");
    $stmt->execute(['id' => $review_id]);
    header("Location:  ../../../product.php?id={$product_id}");
    exit;

} catch (Exception $e) {
    echo $e;
}


