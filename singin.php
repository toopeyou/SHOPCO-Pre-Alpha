<?php
session_start();
if (isset($_SESSION['user_id'])) {
    header('Location: /userpage.php');
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style/sing.css">
</head>

<body>
    <div class="container py-5">
        <div class="" id="loginModal" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Welcome Back!</h5>
                        <a href="/" type="button" class="btn-close" data-bs-dismiss="modal"></a>
                    </div>
                    <div class="modal-body">
                        <form method="post" action="/lib/api/publish/auth.php">
                            <div class="mb-3">
                                <label for="email" class="form-label">Email address</label>
                                <div class="input-group">
                                    <input name="email" type="email" class="form-control"
                                        placeholder="name@example.com">
                                    <span class="input-group-text">
                                        <i class="fas fa-envelope"></i>
                                    </span>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Password</label>
                                <div class="input-group">
                                    <input name="password" type="password" class="form-control"
                                        placeholder="Enter your password">
                                    <span class="input-group-text password-toggle">
                                        <i class="fas fa-eye"></i>
                                    </span>
                                </div>
                            </div>

                            <div class="form-check">

                                <a href="#" class="text-decoration-none">Esqueceu a palavra passe?</a>
                            </div>

                            <button type="submit" class="btn btn-login text-white">Sign In</button>

                            <div class="divider">
                                <span>or continue with</span>
                            </div>

                            <div class="social-login">
                                <button type="button" class="btn btn-social">
                                    <i class="fab fa-google"></i>Google
                                </button>
                                <button type="button" class="btn btn-social">
                                    <i class="fab fa-facebook-f"></i>Facebook
                                </button>
                            </div>

                            <div class="register-link">
                                Don't have an account? <a href="#" class="text-decoration-none">Register now</a>
                            </div>
                        </form>


                    </div>
                </div>
            </div>
        </div>
    </div>
</body>

</html>