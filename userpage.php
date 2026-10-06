<?php
session_start();
require __DIR__ . '/lib/db.php';

if (!isset($_SESSION['user_id'])) {header('Location: singup.php'); exit;}
$translations = [
    'shipped' => 'Доставлен',
    'pending' => 'В ожидании оплаты',
    'delivered' => 'В Доставке',
    'cancel' => 'Отменен',
  ];

$stmt = $pdo->prepare("SELECT name, lastname, email,phone_number FROM users WHERE id = ? ");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

$userId = $_SESSION['user_id'];

$sql = "
    SELECT
        r.id,
        r.content,
        r.rating,
        r.product_id,
        r.CreatedAt,
        p.name AS product_name,
        pi.image_url AS product_image
    FROM reviews r

    LEFT JOIN Users u
        ON u.id = r.user_id

    LEFT JOIN products p
        ON p.id = r.product_id

    LEFT JOIN product_images pi
        ON pi.product_id = r.product_id
        AND pi.is_main = 1

    WHERE r.user_id = :user_id

    ORDER BY r.CreatedAt DESC
    LIMIT 2
";

$stmt = $pdo->prepare($sql);
$stmt->execute([':user_id' => $userId]);
$reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);

$user_id = $_SESSION['user_id'];

$stmt = $pdo->prepare("
    SELECT
        w.id AS wishlist_id,
        p.id AS product_id,
        p.name,
        p.rate,
        p.price,
        pi.image_url
    FROM wishlist AS w
    JOIN products AS p
        ON p.id = w.product_id
    LEFT JOIN product_images AS pi
        ON pi.product_id = p.id
        AND pi.is_main = 1
    WHERE w.user_id = :user_id
    ORDER BY w.id DESC
    LIMIT 3
");

$stmt->execute([
    ':user_id' => $user_id
]);

$wishlist = $stmt->fetchAll(PDO::FETCH_ASSOC);


$stmt = $pdo->prepare("
  SELECT
      o.id AS order_id,
      oi.created_at AS order_date,
      o.total AS order_total,

      oi.id AS order_item_id,
      p.name AS product_name,

      pv.color,
      pv.size,

      oi.quantity,
      oi.price,
      oi.discount,
      oi.total AS item_total,

      os.status,

      pi.image_url AS product_image

  FROM orders o

  JOIN order_items oi
      ON oi.order_id = o.id

  JOIN shipments os
    on os.order_id = o.id

  JOIN products p
      ON p.id = oi.product_id

  JOIN product_variants pv
      ON pv.id = oi.variant_id

  LEFT JOIN product_images pi
      ON pi.product_id = p.id
      AND pi.is_main = 1

  WHERE o.user_id = :user_id

  ORDER BY o.id DESC, oi.id ASC");

$stmt->execute([
    ':user_id' => $user_id
]);

$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

          
$sql = 'SELECT * FROM user_addresses WHERE user_id = ? AND is_default = 1';
$stmt = $pdo->prepare($sql);
$stmt->execute([$user_id]);
$address = $stmt->fetch(PDO::FETCH_ASSOC);
$shippedCount = 0;
$deliveredCount = 0;
$cancelCount = 0;
foreach ($orders as $order) {
    if ($order['status'] === 'cancel') {$cancelCount++;}
    elseif ($order['status'] === 'delivered'){$deliveredCount++;}
    elseif ($order['status'] === 'shipped'){$shippedCount++;}
}
 $groupedOrders = [];
foreach ($orders as $order) {
    $groupedOrders[$order['order_id']][] = $order;
}


if (!$user) {
  exit;
}

?>


<!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Мой аккаунт</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
  <link rel="stylesheet" href="style/reset.css">
  <link rel="stylesheet" href="style/userpage.css">
</head>
<body>
    <?php
    require('blocks/discountheader.php');
    require('blocks/nav.php');
    ?>

  <main class="account-page">
    <div class="d-flex flex-column flex-md-row align-items-md-end justify-content-between gap-3 mb-5">
      <div>
        <h1 class="account-title mb-0">МОЙ АККАУНТ</h1>
      </div>
      <div class="d-inline-flex align-items-center gap-2 bg-light rounded-pill px-3 py-2 align-self-start align-self-md-auto">
        <span class="rounded-circle bg-success" style="width:10px;height:10px;"></span>
        <span class="small">Здравствуйте,<strong><?= htmlspecialchars($_SESSION['name']) ?></strong>!</span>
      </div>
    </div>


    <div class="row g-4 g-xl-5">
      <aside class="col-lg-4 col-xl-3">
        <div class="soft-card p-4 mb-4">
          <div class="text-center border-bottom pb-4">
            <div class="position-relative d-inline-block mb-3"></div>
            <h2 class="h5 fw-bold mb-1"> <?= htmlspecialchars($user['name']) , ' ' ,htmlspecialchars($user['lastname'])?></h2>
            <p class="small text-secondary mb-1"><?= htmlspecialchars($user['email']) ?></p>
            <p class="small fw-semibold mb-3"><?= htmlspecialchars($user['phone_number']) ?></p>
          </div>
          <nav class="account-nav nav flex-column gap-1 pt-3">
            <a href="#orders" class="nav-link active d-flex justify-content-between align-items-center">
              <span><i class="bi bi-bag-check me-3"></i>Мои заказы</span>
              <span class="badge rounded-pill"><?= count($orders)?></span>
            </a>

            <a href="#profile" class="nav-link"><i class="bi bi-person me-3 text-secondary"></i>
              Персональные данные
            </a>

            <a href="#setting-address" class="nav-link d-flex justify-content-between">
              <span><i class="bi bi-geo-alt me-3 text-secondary"></i>Адреса доставки</span><small class="text-secondary">2 адреса</small>
            </a>

            <a href="#payments" class="nav-link d-flex justify-content-between">
              <span>
                <i class="bi bi-credit-card me-3 text-secondary"></i>
                Способы оплаты
              </span>
              <small class="text-secondary">•••• 4242</small>
            </a>

            <a href="#myreviews" class="nav-link d-flex justify-content-between">
              <span>
                <i class="bi bi-heart me-3 text-secondary"></i>
                Избранное
              </span>
              <span class="badge bg-secondary-subtle text-dark rounded-pill">
                6
              </span>
            </a>

            <a href="#myreviews" class="nav-link d-flex justify-content-between">
              <span><i class="bi bi-bell me-3 text-secondary"></i>Мои отзывыaa  </span>
            </a>

            <hr>
            <a href="lib/api/publish/logout.php" class="nav-link text-danger text-start border-0 bg-transparent">
              <i class="bi bi-box-arrow-right me-3"></i>
              Выйти из аккаунта
            </a>
          </nav>

        </div>


      </aside>


      <div class="col-lg-8 col-xl-9">
        <section id="orders" class="mb-5">
          <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-end gap-3 mb-4">

            <div>
              <h2 class="section-title mb-1">
                История заказов
              </h2>

              <p class="small text-secondary mb-0">
                Управляйте текущими посылками и просматривайте электронные чеки
              </p>
            </div>

            <ul class="nav nav-pills flex-nowrap overflow-auto gap-2"
                id="ordersTabs">

              <li class="nav-item">
                <button class="nav-link active rounded-pill px-3 py-2 small" data-bs-toggle="pill"data-bs-target="#all-orders" type="button">Все заказы (<?= count($orders)?>)</button>
              </li>
              <li class="nav-item">
                <button class="nav-link rounded-pill px-3 py-2 small" data-bs-toggle="pill" data-bs-target="#shipping-orders"type="button"> В доставке (<?= htmlspecialchars($deliveredCount) ?>)</button>
              </li>
              <li class="nav-item">
                <button class="nav-link rounded-pill px-3 py-2 small" data-bs-toggle="pill" data-bs-target="#completed-orders" type="button">Доставлены (<?= htmlspecialchars($shippedCount) ?>)</button>
              </li>
              <li class="nav-item">
                <button class="nav-link rounded-pill px-3 py-2 small" data-bs-toggle="pill"data-bs-target="#cancelled-orders" type="button"> Отмененные (<?= htmlspecialchars($cancelCount) ?>)</button>
              </li>
            </ul>

          </div>
          <div class="tab-content">
            <div class="tab-pane fade show active" id="all-orders">
              <?php foreach ($groupedOrders as $orderId => $items): ?>
              <?php
              $order = $items[0];
              ?>
              <?php if ($orders): ?>
                  <?php require('blocks/order-card.php'); ?>
              <?php endif; ?>
              <?php endforeach; ?>
            </div>

            <div class="tab-pane fade" id="shipping-orders">
              <div class="alert alert-light border rounded-4">
                <i class="bi bi-truck me-2"></i>
                Сейчас в доставке находится <?= htmlspecialchars($deliveredCount) ?> заказ.
              </div>
              <?php foreach ($groupedOrders as $orderId => $items): ?>
              <?php
              $order = $items[0];
              ?>
              <?php if ($order['status'] === 'delivered'): ?>
                  <?php require('blocks/order-card.php'); ?>
              <?php endif; ?>
              <?php endforeach; ?>
            </div>

            <div class="tab-pane fade" id="completed-orders">
              <div class="alert alert-light border rounded-4">
                <i class="bi bi-check-circle me-2"></i>
                У вас <?= htmlspecialchars($shippedCount) ?> доставленных заказа.
              </div>
              <?php foreach ($groupedOrders as $orderId => $items): ?>
              <?php
              $order = $items[0];
              ?>
              <?php if ($order['status'] === 'shipped'): ?>
                  <?php require('blocks/order-card.php'); ?>
              <?php endif; ?>
              <?php endforeach; ?>
            </div>

              
            <div class="tab-pane fade" id="cancelled-orders">
              <div class="alert alert-light border rounded-4">
                <i class="bi bi-x-circle me-2"></i> У вас <?= htmlspecialchars($deliveredCount) ?>Отмененных заказов
              </div>
              <?php foreach ($groupedOrders as $orderId => $items): ?>
              <?php
              $order = $items[0];
              ?>
              <?php if ($order['status'] === 'cancel'): ?>
                  <?php require('blocks/order-card.php'); ?>
              <?php endif; ?>
              <?php endforeach; ?>
              </div>
            </div>
          </div>
        </section>




        <section id="setting-address" class="mb-5">
          <h2 class="section-title mb-4">Быстрые настройки</h2>
          <div class="row g-4">
            <div class="col-md-6">
              <div class="soft-card p-4 h-100">

                <div class="d-flex justify-content-between align-items-center mb-4">
                  <span class="small fw-bold text-uppercase"><i class="bi bi-house-door me-2 text-secondary"></i>Основной адрес доставки</span>
                  <span class="badge bg-secondary-subtle text-dark">По умолчанию</span>
                </div>
                <?php if ($address): ?>
                  <h4 class="h6 fw-bold"><?= htmlspecialchars($address['recipient_name']) ?></h4>
                  <p class="small text-secondary">
                    Г.<?= htmlspecialchars($address['city']) ?> , ул. <?= htmlspecialchars($address['title']) ?>, д. <?= htmlspecialchars($address['address']) ?> , кв. <?= htmlspecialchars($address['apartment']) ?>
                  <br>
                  Индекс: <?= htmlspecialchars($address['postcode']) ?>, Россия 
                  </p>
                  <p class="small text-secondary"><i class="bi bi-telephone me-1"></i><?= htmlspecialchars($address['recipient_phone']) ?></p>
                  <div class="d-flex gap-2 mt-4">
                  <button class="btn btn-light rounded-pill btn-sm flex-grow-1">Изменить адрес</button>
                    <button type="button" class="btn btn-secondary-subtle rounded-circle d-flex align-items-center justify-content-center gap-2" data-bs-toggle="modal"data-bs-target="#addAddressModal">
                        <i class="bi bi-plus-lg"></i>
                        <span>Добавить адрес</span>
                    </button>
             
                  </div>                  
                  <?php endif; ?>
                  <?php if (!$address): ?> 
                 
                  <?php endif; ?>

                    <?php
                    $checkout = false;
                    require('blocks/modal/addadress.php');
                    ?>

              </div>
            </div>
          </div>
        </section>



        <section class="mb-5" id="reviews">

          <div class="d-flex justify-content-between align-items-end mb-4">
            <div>
              <h2 class="section-title mb-1">Мои отзывы</h2>
              <p class="small text-secondary mb-0">Отзывы, которые вы оставили о покупках</p>
            </div>
            <a href="#" class="small fw-bold text-dark">Все отзывы<i class="bi bi-arrow-right ms-1"></i> </a>
          </div>

          <div id="myreviews" class="row g-4">
            <?php foreach ($reviews as $review): ?>
            <div class="col-md-6">
              <a href="product.php?id=<?= htmlspecialchars($review['product_id']) ?>" class="review-card">
                <div class="d-flex align-items-center gap-3 mb-3">
                  <img src="lib/api/private/upload/<?= htmlspecialchars($review['product_image']) ?>" class="review-product" alt="">
                  <div class="flex-grow-1">
                    <div class="small fw-bold"><?= htmlspecialchars($review['product_name']) ?></div>
                    <div class="review-stars small">
                      <i class="bi bi-star-fill"></i>
                      <span class="text-secondary ms-1"><?= htmlspecialchars($review['rating']) ?>.0</span>
                    </div>
                  </div>
                  <small class="text-secondary">  <?= htmlspecialchars($review['CreatedAt']) ?></small>
                </div>
                <p class="small text-secondary mb-0"><?= htmlspecialchars($review['content']) ?></p>
                <div class="mt-3">
                </div>
              </a>
            </div>
            <?php endforeach; ?>
          </div>

        </section>


        <section>
          <div class="d-flex justify-content-between align-items-end mb-4">
            <h2 class="section-title mb-0">Избранное</h2>
            <a href="#" class="small fw-bold text-dark">Смотреть все<i class="bi bi-arrow-right ms-1"></i></a>
          </div>
          <div class="row row-cols-2 row-cols-sm-4 g-3">
            <?php foreach ($wishlist as $item): ?>
              <a href="" class="col">
                <div class="recent-product">
                  <img src="lib/api/private/upload/<?= htmlspecialchars($item['image_url']) ?>" alt="" class="mb-3">
                  <div class="small fw-bold text-truncate"><?= htmlspecialchars($item['name']) ?></div>
                  <div class="small text-warning">
                    <i class="bi bi-star-fill"></i>
                    <span class="text-secondary"><?= htmlspecialchars($item['rate']) ?></span>
                  </div>
                  <div class="fw-bold mt-1">₽<?= htmlspecialchars($item['price']) ?></div>
                </div>
              </a>
            <?php endforeach; ?>
          </div>
        </section>

      </div>

    </div>

</main>


<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>