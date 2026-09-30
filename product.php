<?php
session_start();



$id = $_GET['id'] ?? null;
require('lib/db.php');
$sql = "
    SELECT 
        id,
        product_id,
        color,
        size,
        sku,
        hex,
        stock
    FROM product_variants
    WHERE product_id = ?
    ORDER BY color, size
";

$query = $pdo->prepare($sql);
$query->execute([$id]);

$variants = $query->fetchAll(PDO::FETCH_ASSOC);


$variantsJson = json_encode(
    $variants,
    JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
);


$colors = [];

foreach ($variants as $variant) {
    $color = $variant['color'];

    if (!isset($colors[$color])) {
        $colors[$color] = [
            'color' => $color,
            'hex' => $variant['hex']
        ];
    }
}

$sizes = [];

foreach ($variants as $variant) {
    $size = $variant['size'];

    if (!in_array($size, $sizes, true)) {
        $sizes[] = $size;
    }
}
?>


<?php
require('blocks/discountheader.php');
require('blocks/nav.php');
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.0/css/all.min.css">
    <link rel="stylesheet" href="style/reset.css">
    <link rel="stylesheet" href="style/product.css">

</head>

<body>

    <main>
        <section class="container pb-5" data-purpose="product-main-view">
            <div class="row g-4 g-lg-5">
                <div class="col-12 col-lg-6">
                    <div class="d-flex flex-column-reverse flex-md-row gap-3">
                        <div class="d-flex flex-row flex-md-column gap-3 product-thumbnails">
                            <?php
                            $sql = 'SELECT image_url FROM product_images WHERE product_id = ?';
                            $query = $pdo->prepare($sql);
                            $query->execute([$id]);
                            $images = $query->fetchAll(PDO::FETCH_OBJ);
                            foreach ($images as $image) {
                                echo '
                                 <div class="product-thumb active p-1 flex-shrink-0" style="width: 120px; height: 135px;">
                                    <img  src="/lib/api/private/upload/' . $image->image_url . '"
                                    alt="Graphic t-shirt front view" class="product_images w-100 h-100 object-cover rounded-3">
                                </div>
                                ';
                            }
                            ?>
                        </div>
                        <div
                            class="product-main-image bg-brand-gray rounded-4 overflow-hidden d-flex align-items-center justify-content-center p-3">
                            <img src="https://lh3.googleusercontent.com/aida-public/AB6AXuCMlKdpaki1ivkOFfo9tlPSvv4ynB4NuZBQtSpuvMYRlOnM0tX7TFsDBE6UvKNt10kq96syl_RH1av0YWyiF0o9mT2RoWv2lu_cMtu-9Na1KOuV9y6l2ykpSadueILEx27pxzmzVR1tmy9PEjjWV6kKvyhEBYUVVwiBnZaeJdird5HCXRk0G46-lGfMa56StSeif0NU4gCfJPA4NTgQLUIubrTYFbJGKbAnuMjC7eE9wadIGwTtoeysMA"
                                alt="ONE LIFE GRAPHIC T-SHIRT"
                                class="main-image img-fluid object-contain product-main-img">
                        </div>

                    </div>
                </div>


                <div class="col-12 col-lg-6 d-flex flex-column justify-content-between">
                    <div>
                        <?php
                        $sql = 'SELECT * FROM products WHERE id = ?';
                        $query = $pdo->prepare($sql);
                        $query->execute([$id]);
                        $product = $query->fetch(PDO::FETCH_OBJ);
                        $price = $product->price;

                        if ($product->discount > 0) {
                            $price = $price - ($price / 100 * $product->discount);
                        }
                        echo '
                            <h1 class="font-heading product-title text-black mb-3">' . $product->name . '</h1>
                            <div class="d-flex align-items-center gap-2 mb-3">
                            <i class="fa-solid fa-star"></i>
                                <span class="small fw-medium text-black">' . $product->rate . '
                            </div>
                            <div class="d-flex align-items-center gap-3 mb-3">
                                <span class="font-heading product-price text-black">₽' . $price . '</span>' . ($product->discount > 0 ? '<span
                                    class="font-heading product-price text-secondary text-decoration-line-through opacity-40">
                                    ₽' . $product->price . '
                                </span>
                                ' : '') . '

                                ' . ($product->discount > 0 ? '
                                <span class="text-danger badge badge-discount rounded-pill"> ' . $product->discount .
                            '%</span>
                                ' : '') . '
                            </div>
                            <p class="text-secondary product-description mb-4">
                                ' . $product->description . '
                            </p>
                        ';
                        ?>

                        <hr class="text-secondary opacity-25 mb-4">

                        <div class="mb-4">
                            <label class="d-block text-secondary small fw-medium mb-2">Select Color</label>
                            <div id="colorList" class="d-flex gap-3 align-items-center flex-wrap">
                                <?php foreach ($colors as $color): ?>
                                    <button type="button" class="color-swatch border border-secondary p-3"
                                        data-color="<?= htmlspecialchars($color['color']) ?>"
                                        title="<?= htmlspecialchars($color['color']) ?>"
                                        aria-label="<?= htmlspecialchars($color['color']) ?>" style="
                                         background-color: <?= htmlspecialchars($color['hex']) ?>;
                                        ">
                                    </button>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <hr class="text-secondary opacity-25 mb-4">

                        <div class="mb-4">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <label class="d-block text-secondary small fw-medium mb-0">Choose Size</label>
                                <span id="selectedVariantInfo" class="small text-secondary"></span>
                            </div>
                            <div id="sizeList" class="d-flex flex-wrap gap-2">
                                <?php foreach ($sizes as $size): ?>
                                    <button type="button" class="btn btn-size" data-size="<?= htmlspecialchars($size) ?>">
                                        <?= htmlspecialchars($size) ?>
                                    </button>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <hr class="text-secondary opacity-25 mb-4">

                        <div class="d-flex gap-3 align-items-center">
                            <div
                                class="quantity-control d-inline-flex align-items-center bg-brand-gray rounded-pill px-3 py-2">
                                <button id="increaseQuantity">+</button>
                                <span id="quantity" class="fw-bold px-3 small">
                                    1
                                </span>
                                <button id="decreaseQuantity">-</button>
                            </div>

                            <form method="POST" action="/lib/api/publish/addcart.php">
                                <input type="hidden" id="selectedid" name="product_id" value="<?= (int) $id ?>">
                                <input type="hidden" id="selectedVariantId" name="variant_id" value="">
                                <input type="hidden" id="selectedColor" name="color" value="">
                                <input type="hidden" id="selectedSize" name="size" value="">
                                <input type="hidden" id="selectedQuantity" name="quantity" value="1">
                                <button type="submit" id="addToCart"
                                    class="btn btn-dark flex-grow-1 rounded-pill py-3 fw-medium" disabled>
                                    Select color and size
                                </button>

                            </form>

                        </div>
                    </div>
                </div>
            </div>
        </section>


        <section class="container mt-4 mb-5" data-purpose="product-tabs">
            <ul class="nav nav-tabs-shop d-flex mb-4 border-bottom">
                <li class="flex-fill text-center"> <button class="nav-link w-100" type="button">Информация о
                        товаре</button>
                </li>
                <li class="flex-fill text-center"><button class="nav-link active w-100" type="button">Отзывы</button>
                </li>
                <li class="flex-fill text-center"><button class="nav-link w-100" type="button">FAQs</button></li>
            </ul>

            <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
                <?php
                $sql = 'SELECT * FROM reviews WHERE product_id = ? LIMIT 5';
                $query = $pdo->prepare($sql);
                $query->execute([$id]);
                $reviews = $query->fetchAll(PDO::FETCH_OBJ);
                $reviewsCount = $query->fetchColumn();
                ?>
                <div class="d-flex align-items-baseline gap-2">
                    <h2 class="h4 fw-bold text-black m-0"> Всего отзывов </h2>
                    <span class="text-secondary small">(<?= count($reviews) ?>)</span>
                </div>

                <div class="d-flex align-items-center gap-2">
                    <div id="reviewModal" class="modal position-fixed top-0 start-0 w-100 h-100">
                        <div class="position-absolute top-0 start-0 w-100 h-100 bg-dark modal-opacity">
                            <div class="modal-dialog modal-dialog-centered position-relative w-100 mx-3">
                                <div
                                    class="modal-content bg-black border border-secondary rounded shadow-lg overflow-hidden">
                                    <form method="POST" action="/lib/api/publish/review.php"
                                        class="p-4 p-md-5 d-flex flex-column gap-4">
                                        <input type="hidden" name="product_id" value="<?= (int) $id ?>">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <h2 class="h3 text-white text-uppercase fw-bold mb-0">Ваш отзыв</h2>
                                            <button onclick="closeReview()" type="button"
                                                className="btn text-secondary"> X</button>
                                        </div>
                                        <label for="rating" class="color-white">Оценка</label>
                                        <select id="rating" name="rating" required>
                                            <option value="5">★★★★★</option>
                                            <option value="4">★★★★☆</option>
                                            <option value="3">★★★☆☆</option>
                                            <option value="2">★★☆☆☆</option>
                                            <option value="1">★☆☆☆☆</option>
                                        </select>
                                        <div class="d-flex flex-column gap-2">
                                            <textarea id="review" name="review" placeholder="Напишите ваш отзыв..."
                                                required></textarea>
                                        </div>

                                        <div class="d-flex justify-content-end gap-3">
                                            <button type="submit" class="btn btn-danger text-uppercase">Добавить
                                                отзыв</button>
                                        </div>

                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php
                    if (isset($_SESSION['user_id'])) {
                        echo '<button onclick="openReview()" type="button" class="btn btn-dark rounded-pill px-4 py-2 small fw-medium">Написать отзыв</button>';
                    }
                    ?>

                </div>
            </div>
            <div class="row g-4 mb-5">
                <?php foreach ($reviews as $review): ?>
                    <div class="col-12 col-md-6">
                        <div class="review-card h-100">

                            <div class="d-flex justify-content-between align-items-start mb-2">

                                <div class="text-warning">
                                    <i class="bi bi-star-fill"></i>
                                    <i class="bi bi-star-fill"></i>
                                    <i class="bi bi-star-fill"></i>
                                    <i class="bi bi-star-fill"></i>
                                    <i class="bi bi-star-half"></i>
                                </div>

                                <button class="btn p-0 text-secondary border-0">
                                    <i class="bi bi-three-dots"></i>
                                </button>

                            </div>

                            <div class="d-flex align-items-center gap-1 mb-2">
                                <span class="fw-bold text-black">
                                    <?= htmlspecialchars($review->LastName) ?>
                                </span>

                                <i class="bi bi-check-circle-fill text-success small"></i>
                            </div>

                            <p class="text-secondary small review-text mb-3">
                                <?= htmlspecialchars($review->content) ?>


                            </p>
                            <div class="text-secondary review-date fw-medium">
                                <?= htmlspecialchars($review->CreatedAt) ?>

                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>

            </div>

        </section>


    </main>
    <script src="/script/product.js"></script>


    <script>
        const variants = <?= $variantsJson ?>;
        let selectedColor = null;
        let selectedSize = null;
        let quantity = 1;

        function getVariant(color, size) {

            return variants.find(variant => {

                return (
                    variant.color === color &&
                    variant.size === size &&
                    Number(variant.stock) > 0
                );

            });

        }

        function getAvailableSizes(color) {

            return variants
                .filter(variant => {

                    return (
                        variant.color === color &&
                        Number(variant.stock) > 0
                    );

                })
                .map(variant => variant.size);

        }

        function getAvailableColors(size) {

            return variants
                .filter(variant => {

                    return (
                        variant.size === size &&
                        Number(variant.stock) > 0
                    );

                })
                .map(variant => variant.color);

        }

        function updateOptions() {

            let availableSizes = [];

            if (selectedColor) {

                availableSizes = getAvailableSizes(selectedColor);

            } else {

                availableSizes = variants
                    .filter(variant => Number(variant.stock) > 0)
                    .map(variant => variant.size);

            }


            /*
            |--------------------------------------------------------------------------
            | Какие цвета доступны?
            |--------------------------------------------------------------------------
            */

            let availableColors = [];

            if (selectedSize) {

                availableColors = getAvailableColors(selectedSize);

            } else {

                availableColors = variants
                    .filter(variant => Number(variant.stock) > 0)
                    .map(variant => variant.color);

            }


            /*
            |--------------------------------------------------------------------------
            | Убираем дубликаты
            |--------------------------------------------------------------------------
            */

            availableSizes = [...new Set(availableSizes)];
            availableColors = [...new Set(availableColors)];


            /*
            |--------------------------------------------------------------------------
            | Обновляем цвета
            |--------------------------------------------------------------------------
            */

            colorButtons.forEach(button => {

                const color = button.dataset.color;

                const available = availableColors.includes(color);

                button.disabled = !available;

                button.classList.toggle(
                    'disabled',
                    !available
                );

                button.classList.toggle(
                    'active',
                    selectedColor === color
                );

            });


            /*
            |--------------------------------------------------------------------------
            | Обновляем размеры
            |--------------------------------------------------------------------------
            */

            sizeButtons.forEach(button => {

                const size = button.dataset.size;

                const available = availableSizes.includes(size);

                button.disabled = !available;

                button.classList.toggle(
                    'disabled',
                    !available
                );

                button.classList.toggle(
                    'active',
                    selectedSize === size
                );

            });


            /*
            |--------------------------------------------------------------------------
            | Если выбранный цвет больше недоступен
            |--------------------------------------------------------------------------
            */

            if (
                selectedColor &&
                !availableColors.includes(selectedColor)
            ) {

                selectedColor = null;

                selectedColorInput.value = '';

            }


            /*
            |--------------------------------------------------------------------------
            | Если выбранный размер больше недоступен
            |--------------------------------------------------------------------------
            */

            if (
                selectedSize &&
                !availableSizes.includes(selectedSize)
            ) {

                selectedSize = null;

                selectedSizeInput.value = '';

            }


            /*
            |--------------------------------------------------------------------------
            | Проверяем конкретный вариант
            |--------------------------------------------------------------------------
            */

            const variant = getVariant(
                selectedColor,
                selectedSize
            );


            if (variant) {

                selectedVariantId.value = variant.id;

                selectedVariantInfo.textContent =
                    `Stock: ${variant.stock}`;

                addToCartButton.disabled = false;

                addToCartButton.textContent =
                    'Add to Cart';

            } else {

                selectedVariantId.value = '';

                selectedVariantInfo.textContent = '';

                addToCartButton.disabled = true;


                if (!selectedColor && !selectedSize) {

                    addToCartButton.textContent =
                        'Select color and size';

                } else if (!selectedColor) {

                    addToCartButton.textContent =
                        'Select color';

                } else if (!selectedSize) {

                    addToCartButton.textContent =
                        'Select size';

                } else {

                    addToCartButton.textContent =
                        'Unavailable';

                }

            }

        }


        /*
        |--------------------------------------------------------------------------
        | Выбор цвета
        |--------------------------------------------------------------------------
        */

        colorButtons.forEach(button => {

            button.addEventListener('click', function () {

                if (this.disabled) {
                    return;
                }


                const color = this.dataset.color;

                selectedColor = color;

                selectedColorInput.value = color;


                /*
                | Если текущий размер нельзя купить
                | с новым цветом — сбрасываем размер.
                */

                if (
                    selectedSize &&
                    !getVariant(selectedColor, selectedSize)
                ) {

                    selectedSize = null;

                    selectedSizeInput.value = '';

                }


                updateOptions();

            });

        });


        /*
        |--------------------------------------------------------------------------
        | Выбор размера
        |--------------------------------------------------------------------------
        */

        sizeButtons.forEach(button => {

            button.addEventListener('click', function () {

                if (this.disabled) {
                    return;
                }


                const size = this.dataset.size;

                selectedSize = size;

                selectedSizeInput.value = size;


                /*
                | Если текущий цвет нельзя купить
                | с новым размером — сбрасываем цвет.
                */

                if (
                    selectedColor &&
                    !getVariant(selectedColor, selectedSize)
                ) {

                    selectedColor = null;

                    selectedColorInput.value = '';

                }


                updateOptions();

            });

        });


        /*
        |--------------------------------------------------------------------------
        | Quantity
        |--------------------------------------------------------------------------
        */

        decreaseQuantity.addEventListener('click', function () {

            if (quantity > 1) {

                quantity--;

                quantityElement.textContent = quantity;
                selectedQuantityInput.value = quantity
            }

        });


        increaseQuantity.addEventListener('click', function () {
            const variant = getVariant(
                selectedColor,
                selectedSize

            );

            if (!variant) {
                return;
            }


            const stock = Number(variant.stock);


            if (quantity < stock) {

                quantity++;

                quantityElement.textContent = quantity;
                selectedQuantityInput.value = quantity

            }

        });


        /*
        |--------------------------------------------------------------------------
        | Add to cart
        |--------------------------------------------------------------------------
        */

        addToCartButton.addEventListener('click', function () {
            console.log(selectedQuantityInput)
            const variant = getVariant(
                selectedColor,
                selectedSize
            );


            if (!variant) {
                return;
            }



            /*
            | Здесь можно отправить данные в PHP через fetch()
            |
            | variant.id
            | quantity
            |
            */

            console.log({
                product_id: <?= (int) $id ?>,
                variant_id: variant.id,
                color: selectedColor,
                size: selectedSize,
                quantity: quantity
            });

        });


        /*
        |--------------------------------------------------------------------------
        | Первоначальное состояние
        |--------------------------------------------------------------------------
        */

        updateOptions();

    </script>


</body>

</html>