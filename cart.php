<?php
session_start();
require('lib/db.php');

if (!isset($_SESSION['user_id'])) {header('Location: singup.php'); exit('Необходимо авторизоваться');}
$userId = (int) $_SESSION['user_id'];
$showAddAddressModal = isset($_GET['error']) && $_GET['error'] === 'no_address';

$sql = "
SELECT
    ci.variant_id,
    ci.quantity,

    pv.product_id,
    pv.color,
    pv.size,
    pv.stock,
    pv.sku,
    pv.hex,

    p.id,
    p.name,
    p.description,
    p.price,
    p.discount,
    p.rate,
    p.gender,
    p.category,

    pi.image_url AS image

FROM cart_items AS ci

INNER JOIN product_variants AS pv
    ON pv.id = ci.variant_id

INNER JOIN products AS p
    ON p.id = pv.product_id

LEFT JOIN product_images AS pi
    ON pi.product_id = p.id
    AND pi.is_main = 1

WHERE ci.user_id = ?;
";
$stmt = $pdo->prepare($sql);
$stmt->execute([$userId]);
$cartItems = $stmt->fetchAll(PDO::FETCH_OBJ);
$totalprice = 0;
$finalprice = 0;

$sql = 'SELECT * FROM user_addresses WHERE user_id = ? AND is_default = 1';
$stmt = $pdo->prepare($sql);
$stmt->execute([$userId]);
$address = $stmt->fetch(PDO::FETCH_ASSOC);

foreach ($cartItems as $item) {
    $price = (float) $item->price;
    $quantity = (int) $item->quantity;
    $discount = (float) $item->discount;

    $totalprice += $price * $quantity;
    $discountedPrice = $price * (1 - $discount / 100);

    $finalprice += $discountedPrice * $quantity;
}
$discountamout = $totalprice - $finalprice;
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Корзина</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style/reset.css">
    <link rel="stylesheet" href="style/cart.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.0/css/all.min.css">

</head>

<body>
    <?php
        require('blocks/discountheader.php');
        require('blocks/nav.php');
    ?>
    <main class="container px-3 pt-4 pb-5">
        <h1 class="display-5 text-uppercase fw-bold mb-4 mb-md-5">Корзина</h1>

        <div class="row g-4 align-items-start">

            <div class="col-12 col-lg-7">
                <div class="border rounded-4 p-3 p-sm-4 bg-white" data-purpose="cart-items-list">
                    <?php if (!$cartItems): ?>
                        <span>Корзина пуста </span>
                    <?php endif; ?>
                    <?php foreach ($cartItems as $item): ?>
                        <article class="d-flex gap-3 gap-sm-4 pb-4 border-bottom">
                            <a href="product.php?id=<?= htmlspecialchars($item->id) ?>" class="cart-thumbnail rounded-4 bg-light flex-shrink-0 d-flex align-items-center justify-content-center overflow-hidden">
                                <img src="/lib/api/private/upload/<?= htmlspecialchars($item->image) ?>" alt="<?= htmlspecialchars($item->image) ?>">
                            </a>
                            <div class="d-flex flex-column justify-content-between flex-grow-1">
                                <div class="d-flex align-items-start justify-content-between gap-3">
                                    <div>
                                        <h3 class="h6 h5-sm fw-bold mb-1"><?= htmlspecialchars($item->name) ?></h3>
                                        <p class="small text-secondary mb-0">Размер:<span class="text-dark"><?= htmlspecialchars($item->size) ?></span></p>
                                        <p class="small text-secondary mb-0">Цвет:<span class="text-dark"> <?= htmlspecialchars($item->color) ?></span></p>
                                    </div>
                                    <form method="POST" action="/lib/api/publish/removecart.php">
                                        <input type="hidden" id="variant_id" name="variant_id"  value="<?= htmlspecialchars($item->variant_id) ?>">
                                        <button type="submit" aria-label="Remove Gradient Graphic T-shirt" class="btn btn-link text-danger p-0 text-decoration-none" title="Remove Item">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                                <div class="d-flex align-items-center justify-content-between mt-3">
                                    <span class="fw-bold fs-4">₽
                                        <?= htmlspecialchars(
                                            ($item->discount > 0
                                                ? $item->price * (1 - $item->discount / 100)
                                                : $item->price
                                            ) * $item->quantity
                                        ) ?>                                   
                                    </span>
                                    <form method="POST" action="/lib/api/publish/cartchange.php"
                                        class="quantity-control d-flex align-items-center bg-light rounded-pill px-3 py-2 gap-3">
                                        <button type="submit" name="action" value="add" aria-label="Decrease quantity" class="btn btn-link text-dark p-0 text-decoration-none">+</button>
                                        <input type="hidden" id="variant_id" name="variant_id" value="<?= htmlspecialchars($item->variant_id) ?>">
                                        <input type="hidden" name="quantity" value="<?= htmlspecialchars($item->quantity) ?>">
                                        <span class="small fw-semibold"><?= htmlspecialchars($item->quantity) ?></span>
                                        <button type="submit" name="action" value="remove" aria-label="Increase quantity" class="btn btn-link text-dark p-0 text-decoration-none"> -</button>
                                    </form>
                                </div>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php if ($cartItems): ?>
            <aside class="col-12 col-lg-5">
                <div class="border rounded-4 p-4 bg-white" data-purpose="order-summary">
                        <h2 class="h4 fw-bold mb-4">Информация о заказе</h2>
                        <?php if ($address): ?>
                        <div class="border rounded-3 p-3 mb-4">

                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <span class="fw-semibold">Адрес доставки</span>
                                <button type="button" data-bs-toggle="modal" data-bs-target="#deliveryAddressModal" class="btn btn-sm btn-outline-dark rounded-pill px-3">Выбрать другой </button>
                            </div>
                            <div class="d-flex align-items-start gap-2 mb-3">
                                <i class="fa-solid fa-user mt-1 text-secondary"></i>
                                <div>
                                    <div class="fw-semibold"><?= htmlspecialchars($address['recipient_name']) ?></div>
                                    <div class="small text-secondary"><?= htmlspecialchars($address['recipient_phone']) ?></div>
                                </div>
                            </div>
                            <div class="d-flex align-items-start gap-2">
                                <i class="fa-solid fa-location-dot mt-1 text-secondary"></i>
                                <div>
                                    <div class="fw-semibold">Г.<?= htmlspecialchars($address['city']) ?> , ул. <?= htmlspecialchars($address['title']) ?></div>
                                    <div class="small text-secondary">д. <?= htmlspecialchars($address['address']) ?> , кв. <?= htmlspecialchars($address['apartment']) ?></div>
                                </div>
                            </div>

                        </div>
                        <?php endif; ?>


                        <div class="d-flex flex-column gap-3">
                            <div class="d-flex justify-content-between align-items-center text-secondary">
                                <span>Цена</span>
                                <span class="fw-bold fs-5 text-dark"> ₽<?= htmlspecialchars($totalprice) ?></span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center text-secondary">
                                <span>Скидка (- <?= htmlspecialchars(round($discount)) ?>%)</span>
                                <span class="fw-bold fs-5 text-danger">-<?= htmlspecialchars(round($discountamout)) ?></span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center text-secondary">
                                <span>Доставка</span>
                                <span class="fw-bold fs-5 text-dark">₽15</span>
                            </div>

                            <div class="border-top pt-3 d-flex justify-content-between align-items-center">
                                <span>Итоговая цена</span>
                                <span class="fw-bold fs-4">₽<?= htmlspecialchars($finalprice + 15) ?></span>
                            </div>
                        </div>
                        <form method="POST" action="payment.php" >
                        <input type="hidden" name="price" value="<?= htmlspecialchars($finalprice + 15) ?>">
                        <button type="submit"
                            class="btn btn-dark w-100 rounded-pill py-3 mt-3 fw-semibold d-flex align-items-center justify-content-center gap-2">
                            <span>Go to Checkout</span>
                            <i class="bi bi-arrow-right"></i>
                            </button>
                        </form>
                    </div>
                </aside>
                <?php endif; ?>
                <?php
                    $checkout = true;
                    require('blocks/modal/addadress.php');
                ?>             
                <?php
                    require('blocks/modal/SelectAddress.php');
                ?>
        </div>
    </main>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <?php if ($showAddAddressModal): ?>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const modalElement = document.getElementById('addAddressModal');

            if (modalElement) {
                const modal = new bootstrap.Modal(modalElement);
                modal.show();
            }
        });
    </script>
    <?php endif; ?>

</body>


</html>