<?php
session_start();
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}   
require('lib/db.php');

if (!empty($_SESSION['error'])) {$error = $_SESSION['error']; unset($_SESSION['error']);}
$role = $_SESSION['role'] ?? 0;
$user_id = $_SESSION['user_id'] ?? null;
$id = $_GET['id'] ?? null;
if (!$id) {
    header('Location: /');
    exit;
}

$sql = 'SELECT * FROM products WHERE id = ?';
$stmt = $pdo->prepare($sql);
$stmt->execute([$id]);
$product = $stmt->fetch(PDO::FETCH_OBJ);
if (!$product) {
    header('Location: nonfound.php');
    exit;
}
$price = $product->price;

if ($product->discount > 0) {$price = $price - ($price / 100 * $product->discount);}
if ($user_id) {
    $stmt = $pdo->prepare("SELECT product_id FROM wishlist WHERE user_id = :user_id ");
    $stmt->execute([
        ':user_id' => $user_id
    ]);
    $wishlist = $stmt->fetchAll(PDO::FETCH_COLUMN);
} else {
    $wishlist = [];
}
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

$stmt = $pdo->prepare($sql);
$stmt->execute([$id]);
$variants = $stmt->fetchAll(PDO::FETCH_ASSOC);

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


<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($product->name) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style/reset.css">
    <link rel="stylesheet" href="style/product.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>
<body>
    <?php
        require('blocks/discountheader.php');
        require('blocks/nav.php');
    ?>
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
                        <div class="product-main-image bg-brand-gray rounded-4 overflow-hidden d-flex align-items-center justify-content-center p-3">
                            <img src="lib/api/private/upload/<?= $images[0]->image_url ?>" alt="ONE LIFE GRAPHIC T-SHIRT" class="main-image img-fluid object-contain product-main-img">
                        </div>
                    </div>
                </div>
                <div class="col-12 col-lg-6 d-flex flex-column justify-content-between">
                    <div>
                         <h1 class="font-heading product-title text-black mb-3"><?= htmlspecialchars($product->name) ?></h1>
                            <div class="d-flex align-items-center gap-2 mb-3">  
                                <i class="text-warning bi bi-star-fill"></i>
                                <span class="small fw-medium text-black"><?= htmlspecialchars($product->rate) ?>
                            </div>
                            <div class="d-flex align-items-center gap-3 mb-3">
                                <span class="font-heading product-price text-black">₽<?= htmlspecialchars($price) ?></span>
                                <span class="font-heading product-price text-secondary text-decoration-line-through opacity-40">₽<?= htmlspecialchars($product->price) ?></span>
                                <span class="text-danger badge badge-discount rounded-pill"><?= htmlspecialchars($product->discount)?>%</span>
                            </div>
                            <?php $isWishlist = in_array($id, $wishlist); ?>
                            <form method="POST" action="/lib/api/publish/wishlist.php">
                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                                <input type="hidden" name="product_id" value="<?= htmlspecialchars($id) ?>">
                                <button name="action" value="<?= $isWishlist ? 'remove' : 'add' ?>" type="submit" style="background: none !important;" class="border-0 bg-transparent">
                                    <i class="cursor-pointer bi <?= $isWishlist ? 'bi-heart-fill text-danger' : 'bi-heart text-secondary' ?>"></i>
                                </button>
                            </form>

                            <p class="text-secondary product-description mb-4"><?= htmlspecialchars($product->description) ?></p>
                             <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                                <a class="text-danger" href="productedit.php?id=<?=$id?>">Изменить</a>
                            <?php endif; ?> 
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
                            <?php if (count($sizes) == 0 || count($colors) == 0): ?>
                                <span>Нет в наличии</span>
                            <?php endif; ?>
                            <?php if (count($sizes) !== 0 || count($colors) !== 0): ?>
                            <div class="d-flex gap-3 align-items-center">
                                <div class="quantity-control d-inline-flex align-items-center bg-brand-gray rounded-pill px-3 py-2">
                                    <button id="increaseQuantity">+</button>
                                    <span id="quantity" class="fw-bold px-3 small">1</span>
                                    <button id="decreaseQuantity">-</button>
                                </div>

                                <form method="POST" action="/lib/api/publish/addcart.php">
                                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                                    <input type="hidden" id="selectedid" name="product_id" value="<?= (int) $id ?>">
                                    <input type="hidden" id="selectedVariantId" name="variant_id" value="">
                                    <input type="hidden" id="selectedColor" name="color" value="">
                                    <input type="hidden" id="selectedSize" name="size" value="">
                                    <input type="hidden" id="selectedQuantity" name="quantity" value="1">
                                    <button type="submit" id="addToCart" class="btn btn-dark flex-grow-1 rounded-pill py-3 fw-medium" disabled>
                                        Select color and size
                                    </button>
                                </form>
                            </div>
                            <?php endif; ?>
                            <?php if (!empty($error)  && $error === 'Этот товар уже есть в корзине.'): ?>
                                <div class="alert alert-danger">
                                    <?= htmlspecialchars($error) ?>
                                </div>
                            <?php endif; ?>
                    </div>
                </div>
            </div>
        </section>


        <section class="container mt-4 mb-5" data-purpose="product-tabs">
            <ul class="nav nav-tabs-shop d-flex mb-4 border-bottom">
                <li class="flex-fill text-center"><button class="nav-link w-100" type="button">Информация о товаре</button></li>
                <li class="flex-fill text-center"><button class="nav-link active w-100" type="button">Отзывы</button></li>
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
                    <?php if (!empty($error)  && $error === 'Нельзя написать более одиного отзыва одному товару.'): ?>
                        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                    <?php endif; ?>
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
                            </div>
                            <div class="d-flex align-items-center gap-1 mb-2">
                                <span class="fw-bold text-black"><?= htmlspecialchars($review->LastName) ?></span>
                            </div>
                            <p class="text-secondary small review-text mb-3"> <?= htmlspecialchars($review->content) ?></p>
                            <div class="text-secondary review-date fw-medium"><?= htmlspecialchars($review->CreatedAt) ?></div>
                            <?php if (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $review->user_id): ?>
                            <form method="post" action="/lib/api/publish/review.php">
                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                                <input type="hidden" name="review_id" value="  <?= htmlspecialchars($review->id) ?>">
                                <input type="hidden" name="product_id" value="  <?= htmlspecialchars($review->product_id) ?>">
                                <button name="action" value="remove" style="background: none !important;" class="border-0 bg-transparent"><i class="text-danger bi bi-trash-fill"></i></button>
                            </form>
                            <?php endif; ?> 
                            <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                            <form method="post" action="/lib/api/publish/remove_review.php">
                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                                <input type="hidden" name="review_id" value="  <?= htmlspecialchars($review->id) ?>">
                                <input type="hidden" name="product_id" value="  <?= htmlspecialchars($review->product_id) ?>">
                                <button name="action" value="remove" style="background: none !important;" class="border-0 bg-transparent"><i class="text-danger bi bi-trash-fill"></i></button>
                            </form>
                        <?php endif; ?> 
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>
    </main>
    <?php require('blocks/modal/productimages.php'); ?>   
    <?php
        $_SESSION['csrf_token'];
        $product_id = (int) $id;
        require('blocks/modal/review.php');
    ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="/script/product.js"></script>
    <script>
        const variants = <?= $variantsJson ?>;
        function updateOptions() {
            let availableSizes = [];
            if (selectedColor) {
                availableSizes = getAvailableSizes(selectedColor);
            } else {
                availableSizes = variants
                    .filter(variant => Number(variant.stock) > 0)
                    .map(variant => variant.size);
            }
            let availableColors = [];
            if (selectedSize) {
                availableColors = getAvailableColors(selectedSize);
            } else {
                availableColors = variants
                    .filter(variant => Number(variant.stock) > 0)
                    .map(variant => variant.color);

            }
            availableSizes = [...new Set(availableSizes)];
            availableColors = [...new Set(availableColors)];

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

            sizeButtons.forEach(button => {
                const size = button.dataset.size;
                const available = availableSizes.includes(size);
                button.disabled = !available;
                button.classList.toggle('disabled',!available);
                button.classList.toggle('active',selectedSize === size);
            });

            if (
                selectedColor &&
                !availableColors.includes(selectedColor)
            ) {
                selectedColor = null;
                selectedColorInput.value = '';
            }
            if (
                selectedSize &&
                !availableSizes.includes(selectedSize)
            ) {
                selectedSize = null;
                selectedSizeInput.value = '';
            }
            const variant = getVariant(
                selectedColor,
                selectedSize
            );
            if (variant) {
                selectedVariantId.value = variant.id;
                selectedVariantInfo.textContent = `Stock: ${variant.stock}`;
                addToCartButton.disabled = false;
                addToCartButton.textContent ='Add to Cart';

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
        colorButtons.forEach(button => {
            button.addEventListener('click', function () {
                if (this.disabled) {return;}
                const color = this.dataset.color;
                selectedColor = color;
                selectedColorInput.value = color;
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
        sizeButtons.forEach(button => {
            button.addEventListener('click', function () {
                if (this.disabled) {
                    return;
                }
                const size = this.dataset.size;
                selectedSize = size;
                selectedSizeInput.value = size;
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
            if (!variant) {return;}
            const stock = Number(variant.stock);
            if (quantity < stock) {
                quantity++;
                quantityElement.textContent = quantity;
                selectedQuantityInput.value = quantity
            }

        });

        addToCartButton.addEventListener('click', function () {
            console.log(selectedQuantityInput)
            const variant = getVariant(
                selectedColor,
                selectedSize
            );
            if (!variant) {return;}
        });
        updateOptions();
    </script>


</body>

</html>