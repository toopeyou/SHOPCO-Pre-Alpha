<?php
session_start();
require __DIR__ . '/lib/db.php';
$search = trim($_GET['q'] ?? '');
$category = trim($_GET['category'] ?? '');
$color = trim($_GET['color'] ?? '');
$size = trim($_GET['size'] ?? '');
$sort = $_GET['sort'] ?? 'newest';

$minPrice = max(0, (float)($_GET['min_price'] ?? 0));
$maxPrice = max(0, (float)($_GET['max_price'] ?? 1000000));
$minRate = min(5, max(0, (int)($_GET['rate'] ?? 0)));
$onlyDiscount = isset($_GET['discount']);

$conditions = [];
$params = [];

$conditions[] = 'p.price * (1 - p.discount / 100) >= :min_price';
$params['min_price'] = $minPrice;

$conditions[] = 'p.price * (1 - p.discount / 100) <= :max_price';
$params['max_price'] = $maxPrice;

if ($search !== '') {
    $conditions[] = 'p.name LIKE :search';
    $params['search'] = '%' . $search . '%';
}

if ($category !== '') {
    $conditions[] = 'p.category = :category';
    $params['category'] = $category;
}

if ($minRate > 0) {
    $conditions[] = 'p.rate >= :rate';
    $params['rate'] = $minRate;
}

if ($onlyDiscount) {
    $conditions[] = 'p.discount > 0';
}

if ($color !== '' || $size !== '') {
    $conditions[] = "EXISTS (
        SELECT 1
        FROM product_variants v
        WHERE v.product_id = p.id
        AND v.stock > 0
        " . ($color !== '' ? "AND v.color = :color " : "") . "
        " . ($size !== '' ? "AND v.size = :size " : "") . "
    )";

    if ($color !== '') {
        $params['color'] = $color;
    }

    if ($size !== '') {
        $params['size'] = $size;
    }
}

$orderBy = match ($sort) {
    'price_asc'  => 'final_price ASC',
    'price_desc' => 'final_price DESC',
    'popular'    => 'p.rate DESC',
    'name'       => 'p.name ASC',
    default      => 'p.created_at DESC',
};

$sql = "
    SELECT p.*,
        p.price * (1 - p.discount / 100) AS final_price,
        pi.image_url AS image
    FROM products p
    LEFT JOIN product_images pi
        ON pi.product_id = p.id
        AND pi.is_main = 1
    WHERE " . implode(' AND ', $conditions) . "
    ORDER BY $orderBy
";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll(PDO::FETCH_OBJ);

$categories = $pdo->query("
    SELECT DISTINCT category
    FROM products
    WHERE category IS NOT NULL AND category <> ''
    ORDER BY category
")->fetchAll(PDO::FETCH_COLUMN);

$colors = $pdo->query("
    SELECT DISTINCT color
    FROM product_variants
    WHERE color IS NOT NULL AND color <> ''
    ORDER BY color
")->fetchAll(PDO::FETCH_COLUMN);

$sizes = $pdo->query("
    SELECT DISTINCT size
    FROM product_variants
    WHERE size IS NOT NULL AND size <> ''
    ORDER BY size
")->fetchAll(PDO::FETCH_COLUMN);
?>

<!doctype html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Поиск товаров</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="style/reset.css">

   <style>
        body {
            background: #fff;
            color: #111;
        }
        .search-layout {
            display: grid;
            grid-template-columns: 250px minmax(0, 1fr);
            gap: 32px;
        }

        .filter-panel {
            border: 1px solid #e5e5e5;
            border-radius: 16px;
            padding: 22px;
            height: fit-content;
        }

        .filter-section {
            padding: 18px 0;
            border-bottom: 1px solid #eee;
        }

        .filter-section:last-of-type {
            border-bottom: 0;
        }

        .product-card {
            display: block;
            color: #111;
            text-decoration: none;
            height: 100%;
        }

        .product-image {
            aspect-ratio: 1 / 1;
            background: #f2f2f2;
            border-radius: 18px;
            overflow: hidden;
        }

        .product-image img {
            width: 100%;
            height: 100%;
            object-fit: contain;
            transition: transform .25s ease;
        }

        .product-card:hover .product-image img {
            transform: scale(1.05);
        }

        .product-price {
            font-size: 19px;
        }

        .discount-badge {
            color: #c62828;
            background: #ffe6e6;
        }

        .filter-title {
            font-size: 15px;
            font-weight: 700;
        }

        @media (max-width: 767px) {
            .search-layout {
                grid-template-columns: 1fr;
                gap: 20px;
            }

            .filter-panel {
                padding: 16px;
            }

            .products-grid > div {
                width: 50%;
            }
        }
    </style>
</head>
<body>
    <?php
    require('blocks/discountheader.php');
    require('blocks/nav.php');
    ?>
    <main class="container py-4 py-lg-5">
        <h1 class="fw-bold mb-4">Поиск товаров</h1>
        <form method="GET" class="mb-4">
            <div class="input-group input-group-lg">
                <input type="search" name="q" class="form-control" placeholder="Найти товар..." value="<?= htmlspecialchars($search, ENT_QUOTES, 'UTF-8') ?>">
                <button class="btn btn-dark px-4" type="submit"> <i class="bi bi-search"></i> Найти</button>
            </div>
        </form>

        <div class="search-layout">
            <aside>
                <form method="GET" class="filter-panel">
                    <input type="hidden" name="q" value="<?= htmlspecialchars($search, ENT_QUOTES, 'UTF-8') ?>">
                    <div class="d-flex justify-content-between align-items-center">
                        <h2 class="h5 fw-bold mb-0">Фильтры</h2>
                        <a href="search.php" class="small text-secondary">Сбросить</a>
                    </div>
                    <div class="filter-section">
                        <label class="filter-title mb-2" for="category">Категория</label>
                        <select name="category" id="category" class="form-select">
                            <option value="">Все категории</option>
                            <?php foreach ($categories as $item): ?>
                                <option value="<?= htmlspecialchars($item, ENT_QUOTES, 'UTF-8') ?>"
                                    <?= $category === $item ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($item) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="filter-section">
                        <div class="filter-title mb-3">Цена (₽)</div>
                        <div class="d-flex gap-2">
                            <input type="number" name="min_price" class="form-control" min="0" step="1" aria-label="Минимальная цена" value="<?= htmlspecialchars((string)$minPrice) ?>">
                            <input type="number" name="max_price" class="form-control" min="0" step="1" aria-label="Максимальная цена" value="<?= htmlspecialchars((string)$maxPrice) ?>">
                        </div>
                    </div>
                    <div class="filter-section">
                        <label class="filter-title mb-2" for="color"> Цвет</label>

                        <select name="color" id="color" class="form-select">
                            <option value="">Все цвета</option>
                            <?php foreach ($colors as $item): ?>
                                <option value="<?= htmlspecialchars($item, ENT_QUOTES, 'UTF-8') ?>"
                                    <?= $color === $item ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($item) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="filter-section">
                        <label class="filter-title mb-2" for="size">Размер</label>
                        <select name="size" id="size" class="form-select">
                            <option value="">Все размеры</option>
                            <?php foreach ($sizes as $item): ?>
                                <option value="<?= htmlspecialchars($item, ENT_QUOTES, 'UTF-8') ?>"
                                    <?= $size === $item ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($item) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="filter-section">
                        <label class="filter-title mb-2" for="rate">Минимальный рейтинг</label>
                        <select name="rate" id="rate" class="form-select">
                            <option value="0">Любой рейтинг</option>
                            <?php for ($i = 5; $i >= 1; $i--): ?>
                                <option value="<?= $i ?>"
                                    <?= $minRate === $i ? 'selected' : '' ?>>
                                    <?= $i ?> ★ и выше
                                </option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    <div class="filter-section">
                        <input type="checkbox" class="form-check-input" name="discount" value="1" id="discount"
                            <?= $onlyDiscount ? 'checked' : '' ?>>
                        <label for="discount" class="form-check-label ms-2">Только со скидкой</label>
                    </div>

                    <div class="filter-section">
                        <label class="filter-title mb-2" for="sort">Сортировка</label>
                        <select name="sort" id="sort" class="form-select">
                            <option value="newest"
                                <?= $sort === 'newest' ? 'selected' : '' ?>>
                                Сначала новые
                            </option>
                            <option value="price_asc"
                                <?= $sort === 'price_asc' ? 'selected' : '' ?>>
                                Сначала дешевле
                            </option>
                            <option value="price_desc"
                                <?= $sort === 'price_desc' ? 'selected' : '' ?>>
                                Сначала дороже
                            </option>
                            <option value="popular"
                                <?= $sort === 'popular' ? 'selected' : '' ?>>
                                По рейтингу
                            </option>
                            <option value="name"
                                <?= $sort === 'name' ? 'selected' : '' ?>>
                                По названию
                            </option>
                        </select>
                    </div>

                    <button type="submit" class="btn btn-dark w-100 mt-3">Применить фильтры</button>
                </form>
            </aside>

            <section>
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2 class="h5 fw-bold mb-0">Все товары</h2>
                    <span class="text-secondary small">
                        Найдено: <?= count($products) ?>
                    </span>
                </div>

                <?php if (!$products): ?>
                    <div class="text-center py-5">
                        <i class="bi bi-search fs-1 text-secondary"></i>
                        <h3 class="h5 mt-3">Товары не найдены</h3>
                        <p class="text-secondary"> Измени параметры поиска или сбрось фильтры.</p>
                        <a href="search.php" class="btn btn-dark">Показать все товары</a>
                    </div>
                <?php else: ?>
                    <div class="row g-4 products-grid">
                        <?php foreach ($products as $product): ?>
                           <?php
                            require('blocks/product-card.php');
                            ?>
                        <?php endforeach; ?>
                    </div>

                <?php endif; ?>
            </section>

        </div>
    </main>

</body>
</html>