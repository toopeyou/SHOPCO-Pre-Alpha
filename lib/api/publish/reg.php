<?php

declare(strict_types=1);
session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'domain' => '',
    'secure' => !empty($_SERVER['HTTPS']),
    'httponly' => true,
    'samesite' => 'Lax'
]);

require_once('../../db.php');
session_start();
if (
    empty($_SESSION['csrf_token']) ||
    empty($_POST['csrf_token']) ||
    !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])
) {
    $_SESSION['message'] = 'Недействительный запрос. Попробуйте ещё раз.';
    header('Location: ../../../signup.php');
    exit;
}

require_once('../../db.php');

$name = trim($_POST['name'] ?? '');
$lastName = trim($_POST['lastname'] ?? '');
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

if (
    $name === '' ||
    $lastName === '' ||
    $email === '' ||
    $password === ''
) {
    $_SESSION['message'] = 'Заполните все поля';
    header('Location: ../../../signup.php');
    exit;
}


if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $_SESSION['message'] = 'Введите корректную почту';
    header('Location: ../../../signup.php');
    exit;
}

if (mb_strlen($password) < 8) {
    $_SESSION['message'] = 'Пароль должен содержать минимум 8 символов';
    header('Location: ../../../signup.php');
    exit;
}

try {
    $sql = 'SELECT id FROM users WHERE email = ? LIMIT 1';
    $query = $pdo->prepare($sql);
    $query->execute([$email]);

    if ($query->fetch()) {
        $_SESSION['message'] = 'Пользователь с такой почтой уже существует';
        $_SESSION['message_type'] = 'error';
        header('Location: ../../../signup.php');
        exit;
    }
    $passwordHash = password_hash(
        $password,
        PASSWORD_DEFAULT
    );

    if ($passwordHash === false) {
        throw new RuntimeException('Не удалось создать хеш пароля');
    }
    $sql = '
        INSERT INTO users (name, lastname, email, password)
        VALUES (?, ?, ?, ?)
    ';

    $query = $pdo->prepare($sql);
    $query->execute([
        $name,
        $lastName,
        $email,
        $passwordHash
    ]);
    $lastInsertId = $pdo->lastInsertId();

    session_regenerate_id(true);
    $getRoleSql = 'SELECT role from users WHERE id = ?';
    $getRoleQuery = $pdo->prepare($getRoleSql);
    $getRoleQuery->execute([$lastInsertId]);
    $getRole = $getRoleQuery->fetch(PDO::FETCH_ASSOC);
    $_SESSION['user_id'] = $lastInsertId;
    $_SESSION['role'] = $getRole['role'];
    $_SESSION['name'] = $name;
    header('Location: /');

    exit;

} catch (PDOException $e) {
    error_log($e->getMessage());
    if (
        isset($e->errorInfo[0], $e->errorInfo[1]) &&
        $e->errorInfo[0] === '23000' &&
        (int) $e->errorInfo[1] === 1062
    ) {
        $_SESSION['message'] = 'Пользователь с такой почтой уже существует';
        header('Location: ../../../signup.php');
        exit;
    }
    $_SESSION['message'] = 'Произошла ошибка. Попробуйте ещё раз.';
    header('Location: ../../../signup.php');
    exit;

} catch (Throwable $e) {
    error_log($e->getMessage());
    $_SESSION['message'] = 'Произошла ошибка. Попробуйте ещё раз.';
    header('Location: ../../../signup.php');
    exit;
}
