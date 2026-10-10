<?php
session_start();
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
if (isset($_SESSION['user_id'])) {header('Location: /userpage.php');}

?>

<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Регистрация</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.0/css/all.min.css">
    <link rel="stylesheet" href="style/sing.css">
</head>

<body >
    <div class="container py-5">
        <div id="loginModal" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Регистрация</h5>
                        <a href="/" type="button" class="btn-close" data-bs-dismiss="modal"></a>
                    </div>
                    <div class="modal-body">
                        <form method="post" action="/lib/api/publish/reg.php">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') ?>">
                            <div class="mb-3">
                             <label for="email" class="form-label">Имя</label>
                                <div class="input-group">
                                    <input type="text"  name="name" id="firstName" placeholder="Имя" required class="form-control">
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="email" class="form-label">Фамилия</label>
                                <div class="input-group">
                                    <input type="text" name="lastname" id="lastName" placeholder="Фамилия" required class="form-control">
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="email" class="form-label">Эл-почта</label>
                                <div class="input-group">
                                    <input type="email"  name="email" id="email" placeholder="name@example.com" required class="form-control">
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Пароль</label>
                                <div class="input-group">
                                    <input type="password" name="password" id="password" value="" placeholder="Пароль" required class="form-control">
                                </div>
                            </div>     
                               <?php   
                            if (isset($_SESSION['message'])) {
                                echo '<div class="text-danger error">' . htmlspecialchars($_SESSION['message']) . '</div>';
                                unset($_SESSION['message']);
                            }
                            ?>                      
                            <button type="submit" class="btn btn-login text-white">Создать аккаунт</button>
                            <div class="register-link">
                               Есть аккаунт ? <a href="signin.php" class="text-decoration-none">Войти</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>

</html>