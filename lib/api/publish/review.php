<?php
require_once('../../db.php');
session_start();

if (
    empty($_POST['csrf_token']) ||
    empty($_SESSION['csrf_token']) ||
    !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])
) {
    http_response_code(403);
    exit('Неверный CSRF-токен');
}
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    header('Location: ../../../signin.php');
    exit('Необходимо авторизоваться');
}
$productId = (int) ($_POST['product_id'] ?? 0);
$rating = (int) ($_POST['rating'] ?? 0);
$action = ($_POST['action'] ?? 0);
$review = trim($_POST['review'] ?? '');
$name = $_SESSION['name'] ?? '';
$user_id = $_SESSION['user_id'] ?? '';


if ($action === 'add') {
    if ($productId <= 0) {
        die('Некорректный товар.');
    }
    if ($rating < 1 || $rating > 5) {
        die('Некорректная оценка.');
    }
    if ($review === '') {
        die('Отзыв не может быть пустым.');
    }
    if (mb_strlen($review) > 5000) {
        die('Отзыв слишком длинный.');
    }
    try {
       
    }catch (Exception $e) {
        echo $e;
    }
    try {
        $sql = '
            INSERT INTO reviews (
                product_id,
                user_id,
                LastName,
                FirstName,
                content,
                rating
            )
            VALUES (?, ?, ?, ?, ?, ?)
        ';

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $productId,
            $user_id,
            $name,
            $name,
            $review,
            $rating
        ]);
        header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? '/'));
        exit;

    } catch (Exception $e) {
        if ($e->errorInfo[0] === '23000' && $e->errorInfo[1] == 1062) {
        $_SESSION['error'] = 'Нельзя написать более одиного отзыва одному товару.';
            header("Location:  ../../../product.php?id={$productId}");
        } else {
            throw $e;
        }
    }
}
if ($_POST['action'] === 'remove') {
    try{   
    $stmt = $pdo->prepare("
        DELETE FROM reviews
        WHERE user_id = :user_id
          AND product_id = :product_id
    ");

    $stmt->execute([
        ':user_id' => $user_id,
        ':product_id' => $productId
    ]);
    header("Location:  ../../../product.php?id={$productId}");
    exit;
    } catch (Exception $e) {}
}



?>