<?php

declare(strict_types=1);

session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'domain' => '',
    'secure' => !empty($_SERVER['HTTPS']), // true при HTTPS
    'httponly' => true,
    'samesite' => 'Lax'
]);

session_start();

require_once('../../db.php');

$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

if (!filter_var($email, FILTER_VALIDATE_EMAIL) || $password === '') {
    $_SESSION['message'] = 'Введите корректную почту и пароль.';
    header('Location: ../signin.php');
    exit;
}

try {
    $sql = "SELECT id, name, password, role FROM users WHERE email = ?";
    $query = $pdo->prepare($sql);
    $query->execute([$email]);

    $user = $query->fetch(PDO::FETCH_ASSOC);

    if (!$user || !password_verify($password, $user['password'])) {
        $_SESSION['message'] = 'Неверная почта или пароль.';
        header('Location: ../singin.php');
        exit;
    }

    session_regenerate_id(true);

    $_SESSION['user_id'] = (int) $user['id'];
    $_SESSION['role'] = $user['role'];
    $_SESSION['name'] = $user['name'];

    header('Location: /');
    exit;

} catch (PDOException $e) {
    error_log($e->getMessage());

    $_SESSION['message'] = 'Ошибка сервера.';
    header('Location: ../signin.php');
    exit;
}