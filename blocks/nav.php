<?php
session_start();
$role = $_SESSION['role'] ?? 0;
?>

<nav class="d-flex justify-content-between align-items-center p-4">
    <h3 class="logo"><a href="/">SHOP.CO</a></h3>
    <ul class="d-none d-lg-flex align-items-center gap-4 small fw-medium text-black">
        <li><a href="/">Главная</a></li>
        <li>Скидки</li>
        <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
        <li>
            <a href="addproduct.php">Админка</a>
        </li>
        <?php endif; ?>
        <li>Бренды</li>
    </ul>
    <div class="flex-grow-1 mx-2 d-none d-sm-block" style="max-width: 36rem;">
        <div class="position-relative w-100">
            <span class="position-absolute top-50 start-0 translate-middle-y ps-3 text-secondary pe-none">
                <i class="fa-solid fa-magnifying-glass"></i>
            </span>
            <!-- <input type="text" class="form-control rounded-pill border-0 ps-5 pe-3 py-2" placeholder="Поисковик" style="background-color: #F0F0F0;"> -->
            <form action="https://shop.co/search.php" method="GET">
                <div class="input-group input-group-lg">
                    <input   type="search"  name="q" class="form-control" placeholder="Найти товар..." value="<?= htmlspecialchars($_GET['q'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                    <button class="btn btn-dark px-4" type="submit">
                        <i class="bi bi-search"></i> Найти
                    </button>
                </div>
            </form>
        </div>
    </div>


    <div class="nav-icons d-flex gap-2">
        <a href="signup.php"><i class="bi bi-person-fill"></i></a>
        <a href="cart.php"><i class="bi bi-cart2"></i></a>
    </div>
</nav>