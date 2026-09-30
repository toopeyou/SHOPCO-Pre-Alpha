<?php
session_start();

require('lib/db.php');

if (!isset($_SESSION['user_id'])) {
    header('Location: singup.php');
    exit('Необходимо авторизоваться');
}

$userId = (int) $_SESSION['user_id'];


?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Cart</title>
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
    <?php
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
    $query = $pdo->prepare($sql);
    $query->execute([$userId]);

    $cartItems = $query->fetchAll(PDO::FETCH_OBJ);
    $totalprice = 0;
    $finalprice = 0;
    for ($i = 0; $i < count($cartItems); $i++) {
        $totalprice += $cartItems[$i]->price * $cartItems[$i]->quantity;
        $finalprice += $cartItems[$i]->discount > 0 ? ($cartItems[$i]->price - ($cartItems[$i]->price / 100 * $cartItems[$i]->discount)) * $cartItems[$i]->quantity : $cartItems[$i]->price * $cartItems[$i]->quantity;
    }
    $discount = 10;
    $discountamout = $totalprice - $finalprice
        ?>


    <main class="container px-3 pt-4 pb-5">
        <nav aria-label="Breadcrumb" class="mb-4">
            <ol class="breadcrumb mb-0 small">
                <li class="breadcrumb-item">
                    <a href="#" class="text-decoration-none text-secondary">Home</a>
                </li>
                <li class="breadcrumb-item active" aria-current="page">
                    Cart
                </li>
            </ol>
        </nav>
        <h1 class="display-5 text-uppercase fw-bold mb-4 mb-md-5"> Your Cart</h1>

        <div class="row g-4 align-items-start">

            <div class="col-12 col-lg-7">
                <div class="border rounded-4 p-3 p-sm-4 bg-white" data-purpose="cart-items-list">


                    <?php foreach ($cartItems as $item): ?>
                        <article class="d-flex gap-3 gap-sm-4 pb-4 border-bottom">
                            <a href="product.php?id=<?= htmlspecialchars($item->id) ?>"
                                class="cart-thumbnail rounded-4 bg-light flex-shrink-0 d-flex align-items-center justify-content-center overflow-hidden">
                                <img src="/lib/api/private/upload/<?= htmlspecialchars($item->image) ?>" alt="">
                            </a>
                            <div class="d-flex flex-column justify-content-between flex-grow-1">

                                <div class="d-flex align-items-start justify-content-between gap-3">
                                    <div>
                                        <h3 class="h6 h5-sm fw-bold mb-1">
                                            <?= htmlspecialchars($item->name) ?>
                                        </h3>

                                        <p class="small text-secondary mb-0">
                                            Размер:
                                            <span class="text-dark">
                                                <?= htmlspecialchars($item->size) ?>
                                            </span>
                                        </p>

                                        <p class="small text-secondary mb-0">
                                            Цвет:
                                            <span class="text-dark"> <?= htmlspecialchars($item->color) ?></span>
                                        </p>
                                    </div>
                                    <form method="POST" action="/lib/api/publish/removecart.php">

                                        <input type="hidden" id="variant_id" name="variant_id"
                                            value="<?= htmlspecialchars($item->variant_id) ?>">
                                        <button type="submit" aria-label="Remove Gradient Graphic T-shirt"
                                            class="btn btn-link text-danger p-0 text-decoration-none" title="Remove Item">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>

                                    </form>
                                </div>
                                <div class="d-flex align-items-center justify-content-between mt-3">
                                    <span class="fw-bold fs-4">
                                        <?= htmlspecialchars(($item->discount > 0 ? $item->price / 100 * $item->discount : $item->price) * $item->quantity) ?>
                                    </span>
                                    <div
                                        class="quantity-control d-flex align-items-center bg-light rounded-pill px-3 py-2 gap-3">
                                        <button type="button" aria-label="Decrease quantity"
                                            class="btn btn-link text-dark p-0 text-decoration-none">
                                            +
                                        </button>

                                        <span class="small fw-semibold">
                                            <?= htmlspecialchars($item->quantity) ?>
                                        </span>

                                        <button type="button" aria-label="Increase quantity"
                                            class="btn btn-link text-dark p-0 text-decoration-none">
                                            -
                                        </button>
                                    </div>
                                </div>

                            </div>
                        </article>
                    <?php endforeach; ?>

                </div>
            </div>

            <aside class="col-12 col-lg-5">
                <div class="border rounded-4 p-4 bg-white" data-purpose="order-summary">

                    <h2 class="h4 fw-bold mb-4">
                        Информация о заказе
                    </h2>

                    <div class="d-flex flex-column gap-3">

                        <div class="d-flex justify-content-between align-items-center text-secondary">
                            <span>Цена</span>
                            <span class="fw-bold fs-5 text-dark">
                                <?= htmlspecialchars($totalprice) ?>
                            </span>
                        </div>

                        <div class="d-flex justify-content-between align-items-center text-secondary">
                            <span>Скидка (- <?= htmlspecialchars(round($discount)) ?>%)</span>
                            <span
                                class="fw-bold fs-5 text-danger">-<?= htmlspecialchars(round($discountamout)) ?></span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center text-secondary">
                            <span>Доставка</span>
                            <span class="fw-bold fs-5 text-dark">$15</span>
                        </div>

                        <div class="border-top pt-3 d-flex justify-content-between align-items-center">
                            <span>Итоговая цена</span>
                            <span class="fw-bold fs-4"><?= htmlspecialchars($finalprice + 15) ?></span>
                        </div>

                    </div>


                    <div class="input-group mt-4">

                        <span class="input-group-text bg-light border-0 rounded-start-pill ps-3">
                            <i class="bi bi-tag"></i>
                        </span>

                        <input type="text" class="form-control bg-light border-0 py-3" placeholder="Add promo code"
                            aria-label="Promo code" />

                        <button type="button" class="btn btn-dark rounded-end-pill px-4">
                            Apply
                        </button>

                    </div>
                    <button type="button"
                        class="btn btn-dark w-100 rounded-pill py-3 mt-3 fw-semibold d-flex align-items-center justify-content-center gap-2">
                        <span>Go to Checkout</span>
                        <i class="bi bi-arrow-right"></i>
                    </button>

                </div>
            </aside>

        </div>
    </main>
</body>

</html>