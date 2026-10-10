<?php session_start();
require('lib/db.php');
function getProducts($pdo, $order = 'newest', $limit = 4)
{
    $orders = [
        'newest' => 'p.id DESC',
        'discount' => 'p.discount DESC',
        'price_asc' => 'p.price ASC',
        'price_desc' => 'p.price DESC',
        'rating' => 'p.rate DESC'
    ];
    if (!isset($orders[$order])) {
        $order = 'newest';
    }
    $limit = (int) $limit;
    if ($limit <= 0) { $limit = 4;}
    $sql = "
        SELECT 
            p.*,
            pi.image_url AS image
        FROM products p
        LEFT JOIN product_images pi
            ON pi.product_id = p.id
            AND pi.is_main = 1
        ORDER BY {$orders[$order]}
        LIMIT {$limit}
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_OBJ);
}
$newsProducts = getProducts($pdo, 'newest', 4);
$discountProducts = getProducts($pdo, 'discount', 4);
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>SHOP.CO</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style/reset.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="style/index.css">
</head>

<body>

    <?php
        require('blocks/discountheader.php');
        require('blocks/nav.php');
    ?>

    <main>
        <section class="hero-section position-relative overflow-hidden">
            <div class="container hero-container">
                <div class="row align-items-center hero-row">
                    <div class="col-12 col-lg-7 hero-content">

                        <h1 class="font-integral display-hero text-black mb-4">
                            Найдите одежду, которая соответствует вашему стилю.
                        </h1>

                        <p class="hero-description text-black-50 mb-4">
                            Ознакомьтесь с нашим разнообразным ассортиментом
                            тщательно сшитой одежды, созданной для того,
                            чтобы подчеркнуть вашу индивидуальность
                            и удовлетворить ваш вкус.
                        </p>

                        <div class="mb-4">
                            <a href="search.php" class="btn btn-dark hero-btn fw-semibold">
                                Показать все
                            </a>
                        </div>

                        <!-- Статистика -->
                        <div class="row row-cols-2 row-cols-sm-3 g-3 hero-stats">

                            <div class="col">
                                <div class="stat-item">
                                    <span class="d-block fw-bold stats-number">
                                        200+
                                    </span>
                                    <p class="mb-0">
                                        Высококачественная продукция
                                    </p>
                                </div>
                            </div>

                            <div class="col">
                                <div class="stat-item">
                                    <span class="d-block fw-bold stats-number">
                                        2000+
                                    </span>
                                    <p class="mb-0">
                                        Международных брендов
                                    </p>
                                </div>
                            </div>

                            <div class="col">
                                <div class="stat-item">
                                    <span class="d-block fw-bold stats-number">
                                        30 000+
                                    </span>
                                    <p class="mb-0">
                                        Довольных клиентов
                                    </p>
                                </div>
                            </div>

                        </div>

                    </div>
                </div>
            </div>
        </section>
        <section class="brands-section bg-black">
            <div class="brands container">

                <div class="brands-list">

                    <img src="./images/Versace.png" alt="Versace">
                    <img src="./images/Zara.png" alt="Zara">
                    <img src="./images/Gucci.png" alt="Gucci">
                    <img src="./images/PRADA.png" alt="Prada">
                    <img src="./images/Calvin.png" alt="Calvin Klein">

                </div>

            </div>
        </section>
        <section id="new-arrivals" class="new-arrivals-section">
            <div class="container text-center">

                <h2 class="font-integral text-black new-arrivals-title">
                    Новые товары
                </h2>

                <div class="row g-4 text-start">

                    <?php foreach ($newsProducts as $product): ?>
                        <?php require('blocks/product-card.php'); ?>
                    <?php endforeach; ?>

                </div>

                <div class="mt-4">
                    <a href="search.php" class="btn btn-outline-dark view-all-btn">
                        Показать все
                    </a>
                </div>

            </div>

            <div class="container mt-5">
                <hr class="section-divider">
            </div>
        </section>
        <section id="top-discounts" class="new-arrivals-section">
            <div class="container text-center">

                <h2 class="font-integral text-black new-arrivals-title">
                    Топ скидок
                </h2>

                <div class="row g-4 text-start">

                    <?php foreach ($discountProducts as $product): ?>
                        <?php require('blocks/product-card.php'); ?>
                    <?php endforeach; ?>

                </div>

                <div class="mt-4">
                    <a href="search.php" class="btn btn-outline-dark view-all-btn">
                        Показать все
                    </a>
                </div>

            </div>

            <div class="container mt-5">
                <hr class="section-divider">
            </div>
        </section>


        <?php require('blocks/footer.php'); ?>

    </main>


    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>