<?php
require_once('../../db.php');
session_start();


$productId = (int) ($_POST['product_id'] ?? 0);
$rating = (int) ($_POST['rating'] ?? 0);
$review = trim($_POST['review'] ?? '');
$name = $_SESSION['name'] ?? '';
$user_id = $_SESSION['user_id'] ?? '';



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

    $query = $pdo->prepare($sql);

    $query->execute([
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



?>