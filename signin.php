<?php
session_start();
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
if (isset($_SESSION['user_id'])) {
    header('Location: /userpage.php');
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Вход в аккаунт</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style/sing.css">
</head>
<body>
    <div class="container py-5">
        <div id="loginModal" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Добро пожаловать назад!</h5>
                        <a href="/" type="button" class="btn-close" data-bs-dismiss="modal"></a>
                    </div>
                    <div class="modal-body">
                        <form method="post" action="/lib/api/publish/auth.php">
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') ?>">
  
                        <div class="mb-3">
                                <label for="email" class="form-label">Эл-почта</label>
                                <div class="input-group">
                                    <input name="email" type="email" class="form-control" placeholder="name@example.com">
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Пароль</label>
                                <div class="input-group">
                                    <input name="password" type="password" class="form-control" placeholder="Введите пароль">
                                </div>
                            </div>               
                            <?php   
                            if (isset($_SESSION['message'])) {
                                echo '<div class="text-danger error">' . htmlspecialchars($_SESSION['message']) . '</div>';
                                unset($_SESSION['message']);
                            }
                            ?>         
                            <button type="submit" class="btn btn-login text-white">Войти</button>
                            <div class="register-link">
                               Нет аккаунта ? <a href="signup.php" class="text-decoration-none">Создать аккаунт</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>

</html>