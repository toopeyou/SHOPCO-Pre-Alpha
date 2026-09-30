<?php
session_start();
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SHOP.CO</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style/reset.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.0/css/all.min.css">
    <link rel="stylesheet" href="style/index.css">
</head>

<body>
    <?php
    require('blocks/discountheader.php');
    require('blocks/nav.php');
    ?>
    <main>
        <section class="position-relative overflow-hidden pt-5 pt-sm-5 pt-lg-5" style="background-color: #F2F0F1;">
            <div class="container hero-container">
                <div class="row align-items-center hero-row">
                    <div
                        class="col-12 col-lg-7 d-flex flex-column justify-content-center pb-4 pb-lg-5 position-relative z-2">
                        <h1 class="font-integral display-hero text-black mb-4">Найдите одежду, которая соответствует
                            вашему стилю.</h1>
                        <p class="text-black-60 fs-6 mb-4">
                            Ознакомьтесь с нашим разнообразным ассортиментом тщательно сшитой одежды, созданной для
                            того, чтобы подчеркнуть вашу индивидуальность и удовлетворить ваш вкус.
                        </p>
                        <div class="mb-5 text-decaration-none">
                            <a class="btn btn-dark hero-btn fw-semibold text-center">Показать все</a>
                        </div>
                        <div class="d-flex gap-5 ">
                            <div class="border-end border-gray-300 col-6 col-sm-4 pe-3">
                                <span class="d-block fw-bold stats-number text-black">200+</span>
                                <p>Высококачественная продукция
                                </p>
                            </div>
                            <div class="border-end border-gray-300 col-6 col-sm-4 pe-3">
                                <span class="d-block fw-bold stats-number text-black">2000+</span>
                                <p>Международных брендов</p>
                            </div>
                            <div class="bcol-6 col-sm-4 pe-3">
                                <span class="d-block fw-bold stats-number text-black">30 000+</span>
                                <p>Довольных клиентов</p>
                            </div>
                        </div>
                    </div>
                    <div
                        class="col-12 col-lg-5 position-relative d-flex justify-content-center align-items-end hero-image-column">
                        <span class="position-absolute top-0 hero-star hero-star-top text-black z-3">
                            <i class="fa-solid fa-star"></i>
                        </span>
                        <span class="position-absolute hero-star hero-star-middle text-black z-3">
                            <i class="fa-solid fa-star"></i>
                        </span>


                    </div>
                </div>
            </div>
        </section>

        <section class="pt-5 pb-5 bg-black">
            <div class="brands d-flex gap-20">
                <div class=" d-flex gap-5">
                    <img src="./images/Versace.png" alt="">
                    <img src="./images/Zara.png" alt="">
                    <img src="./images/Gucci.png" alt="">
                    <img src="./images/PRADA.png" alt="">
                    <img src="./images/Calvin.png" alt="">
                </div>
            </div>
        </section>

        <section id="new-arrivals" class="py-5 py-sm-5 new-arrivals-section">
            <div class="container text-center">
                <h2 class="font-integral text-black mb-5 mb-sm-5 new-arrivals-title">Новые товары </h2>
                <div class="row g-4 text-start">
                    <?php
                    require('lib/db.php');
                    $sql = "SELECT p.*, pi.image_url AS image FROM products p
                        LEFT JOIN product_images pi
                            ON pi.product_id = p.id
                            AND pi.is_main = 1
                        ORDER BY p.id DESC
                        LIMIT 4
                        ";
                    $query = $pdo->prepare($sql);
                    $query->execute();
                    $products = $query->fetchAll(PDO::FETCH_OBJ);
                    foreach ($products as $product) {
                        $price = $product->price;

                        if ($product->discount > 0) {
                            $price = $price - ($price / 100 * $product->discount);
                        }
                        echo '
                        <div class="col-12 col-sm-6 col-lg-3">
                            <a href="product.php?id=' . $product->id . '"  class="product-card">
                                <div
                                    class="product-image bg-product rounded-20 overflow-hidden mb-3 d-flex align-items-center justify-content-center p-4">
                                    <img src="/lib/api/private/upload/' . $product->image . '"
                                        alt="Sleeve Striped T-shirt" class="w-100 h-100 object-contain mix-blend-multiply">
                                </div>
                                <h3 class="fw-bold fs-6 text-black mb-1 text-truncate">' . $product->name . '</h3>
                                <div class="d-flex align-items-center gap-2 mb-2">
                                    <span class="small text-secondary fw-medium">' . $product->rate . '
                                        <span class="text-body-tertiary">
                                            <i class="fa-solid fa-star"></i>
                                        </span>
                                    </span>
                                </div>

                                <div class="d-flex align-items-center gap-2">
                                    <span class="fw-bold product-price text-black">$' . $price . '</span>
                                    ' . ($product->discount > 0 ? '
                                        <span class="fw-bold product-price text-secondary text-decoration-line-through">
                                            ' . $product->price . '
                                        </span>
                                    ' : '') . '


                                    ' . ($product->discount > 0 ? '
                                            <span class="badge discount-badge">
                                            -' . $product->discount . ' %
                                            </span>
                                    ' : '') . '
                                    
                                </div>
                            </a>
                        </div>
                        ';
                    }
                    ?>
                </div>
                <div class="mt-5">
                    <a href="#" class="btn btn-outline-dark view-all-btn">Показать все</a>
                </div>
            </div>
            <div class="container mt-5">
                <hr class="section-divider">
            </div>
        </section>

        <section id="new-arrivals" class="py-5 py-sm-5 new-arrivals-section">
            <div class="container text-center">
                <h2 class="font-integral text-black mb-5 mb-sm-5 new-arrivals-title">Топ скидок</h2>
                <div class="row g-4 text-start">
                    <?php
                    $sql = "SELECT p.*, pi.image_url AS image FROM products p
                        LEFT JOIN product_images pi
                            ON pi.product_id = p.id
                            AND pi.is_main = 1
                        ORDER BY p.discount DESC
                        LIMIT 4
                        ";
                    $query = $pdo->prepare($sql);
                    $query->execute();
                    $products = $query->fetchAll(PDO::FETCH_OBJ);
                    foreach ($products as $product) {
                        $price = $product->price;
                        if ($product->discount > 0) {
                            $price = $price / 100 * $product->discount;
                        }
                        echo '
                        <div class="col-12 col-sm-6 col-lg-3">
                            <a href="product.php?id=' . $product->id . '" class="product-card">
                                <div
                                    class="product-image bg-product rounded-20 overflow-hidden mb-3 d-flex align-items-center justify-content-center p-4">
                                    <img src="lib/upload/' . $product->image . '"
                                        alt="Sleeve Striped T-shirt" class="w-100 h-100 object-contain mix-blend-multiply">
                                </div>
                                <h3 class="fw-bold fs-6 text-black mb-1 text-truncate">' . $product->name . '</h3>
                                <div class="d-flex align-items-center gap-2 mb-2">
                                    <span class="small text-secondary fw-medium">' . $product->rate . '
                                        <span class="text-body-tertiary">           
                                            <i class="fa-solid fa-star"></i>
                                        </span>
                                    </span>
                                </div>

                                <div class="d-flex align-items-center gap-2">
                                    <span class="fw-bold product-price text-black">$' . $price . '</span>
                                    ' . ($product->discount > 0 ? '
                                        <span class="fw-bold product-price text-secondary text-decoration-line-through">
                                            ' . $product->price . '
                                        </span>
                                    ' : '') . '


                                    ' . ($product->discount > 0 ? '
                                            <span class="badge discount-badge">
                                            -' . $product->discount . ' %
                                            </span>
                                    ' : '') . '
                                </div>

                            </a>
                        </div>
                        ';
                    }
                    ?>
                </div>

                <div class="mt-5">
                    <a href="search.php" class="btn btn-outline-dark view-all-btn">Показать все</a>
                </div>

            </div>
            <div class="container mt-5">
                <hr class="section-divider">
            </div>

        </section>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function checkEmail() {
            let email = document.querySelector('#emailField').value;
            if (!email.includes('@')) alert('Нет символа @');
            else if (!email.includes('.')) alert('Нет символа .');
            else alert('Все отлично!');
        }
    </script>
</body>

</html>