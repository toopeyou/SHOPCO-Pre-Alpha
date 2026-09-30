<?php
require __DIR__ . '/lib/db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: singup.php');
    exit;
}

$user_id = (int) $_SESSION['user_id'];

$sql = 'SELECT role FROM users WHERE id = ? LIMIT 1';

$query = $pdo->prepare($sql);
$query->execute([$user_id]);

$role = $query->fetch(PDO::FETCH_ASSOC);

if (!$role || $role['role'] !== 'admin') {
    http_response_code(403);
    header('Location: /');

    exit('Доступ запрещён');
}

$id = $_GET['id'] ?? null;
if (!$id) {
    header('Location: products.php');
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
$stmt->execute([$id]);
$details = $stmt->fetch(PDO::FETCH_OBJ);

if (!$details) {
    header('Location: products.php');
    exit;
}


$stmt = $pdo->prepare("SELECT * FROM product_variants WHERE product_id = ?");
$stmt->execute([$id]);
$variants = $stmt->fetchAll(PDO::FETCH_OBJ);
?>
<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Редактирование товара - Admin Panel</title>
    <link rel="stylesheet" href="style/reset.css">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/media.css">
</head>

<body class="bg-light min-vh-100 d-flex flex-column justify-content-between">
    <header class="bg-white border-bottom sticky-top">
        <div class="container-fluid px-4">
            <div class="d-flex align-items-center justify-content-between py-3">
                <div class="d-flex align-items-center gap-3">
                    <a href="products.php"
                        class="btn btn-outline-secondary rounded-circle p-2 d-flex align-items-center justify-content-center"
                        style="width: 40px; height: 40px;">
                        <i class="bi bi-arrow-left fs-5"></i>
                    </a>
                    <div>
                        <h1 class="h4 mb-0 fw-bold text-dark">Редактирование товара</h1>
                        <p class="text-secondary small mb-0">ID: #<?= htmlspecialchars($details->id) ?></p>
                    </div>
                </div>

            </div>
        </div>
    </header>
    <main class="container-fluid px-4 py-4">

        <?php if (isset($_GET['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show rounded-4 mb-4" role="alert">
                Изменения успешно сохранены!
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <div class="row g-4">

            <div class="col-12 col-lg-8">
                <article class="bg-white border rounded-4 p-4 p-sm-5 shadow-sm mb-4">
                    <div class="d-flex align-items-center gap-3 mb-4">
                        <div class="bg-primary-subtle text-primary rounded-3 p-3 d-flex align-items-center justify-content-center"
                            style="width: 48px; height: 48px;">
                            <i class="bi bi-info-circle fs-4"></i>
                        </div>
                        <div>
                            <h2 class="h5 mb-0 fw-bold text-dark">Основные параметры</h2>
                            <p class="text-secondary small mb-0">Название, категория и описание товара</p>
                        </div>
                    </div>

                    <form class="row g-4" method="post" action="/lib/api/private/update_product.php">
                        <input type="hidden" name="action_type" value="edit_details">
                        <input type="hidden" name="productid" value="<?= $details->id ?>">
                        <div class="col-12">
                            <label class="form-label small fw-semibold text-uppercase text-secondary">Название
                                товара</label>
                            <input name="title" type="text" class="form-control rounded-4 py-3 px-4 fw-medium"
                                value="<?= htmlspecialchars($details->name) ?>" required>
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-semibold text-uppercase text-secondary">Категория</label>
                            <select name="category" class="form-select rounded-4 py-3 px-4 fw-medium">
                                <option value="tshirts" <?= $details->category === 'tshirts' ? 'selected' : '' ?>>T-Shirts
                                </option>
                                <option value="shirts" <?= $details->category === 'shirts' ? 'selected' : '' ?>>Shirts
                                </option>
                                <option value="jeans" <?= $details->category === 'jeans' ? 'selected' : '' ?>>Jeans
                                </option>
                                <option value="hoodies" <?= $details->category === 'hoodies' ? 'selected' : '' ?>>Hoodies
                                </option>
                                <option value="shorts" <?= $details->category === 'shorts' ? 'selected' : '' ?>>Shorts
                                </option>
                            </select>
                        </div>

                        <div class="col-12">
                            <label class="form-label small fw-semibold text-uppercase text-secondary">Описание
                                товара</label>
                            <textarea name="desc" class="form-control rounded-4 p-4 fw-medium"
                                rows="4"><?= htmlspecialchars($details->description) ?></textarea>
                        </div>
                        <div class="col-12 text-end">
                            <button type="submit" class="btn btn-primary rounded-pill px-4 py-2">Сохранить инфо</button>
                        </div>
                    </form>
                </article>

                <article class="bg-white border rounded-4 p-4 p-sm-5 shadow-sm mb-4">
                    <div class="d-flex align-items-center gap-3 mb-4">
                        <div class="bg-success-subtle text-success rounded-3 p-3 d-flex align-items-center justify-content-center"
                            style="width: 48px; height: 48px;">
                            <i class="bi bi-tag fs-4"></i>
                        </div>
                        <div>
                            <h2 class="h5 mb-0 fw-bold text-dark">Ценообразование</h2>
                            <p class="text-secondary small mb-0">Базовая стоимость и скидочная политика</p>
                        </div>
                    </div>

                    <form class="row g-4" method="post" action="/lib/api/private/update_product.php">
                        <input type="hidden" name="action_type" value="edit_price">
                        <input type="hidden" name="productid" value="<?= $details->id ?>">
                        <div class="col-12 col-sm-6">
                            <label class="form-label small fw-semibold text-uppercase text-secondary">Базовая цена
                                ($)</label>
                            <div class="input-group">
                                <span
                                    class="input-group-text rounded-start-4 border-end-0 bg-light text-secondary ps-4">$</span>
                                <input name="price" type="number" step="0.01"
                                    class="form-control rounded-end-4 py-3 px-4 fw-medium"
                                    value="<?= htmlspecialchars($details->price) ?>" required>
                            </div>
                        </div>
                        <div class="col-12 col-sm-6">
                            <label class="form-label small fw-semibold text-uppercase text-secondary">Скидка (%)</label>
                            <div class="input-group">
                                <input name="discount" type="number" min="0" max="100"
                                    class="form-control rounded-start-4 py-3 px-4 fw-medium"
                                    value="<?= htmlspecialchars($details->discount) ?>">
                                <span
                                    class="input-group-text rounded-end-4 border-start-0 bg-light text-secondary pe-4">%</span>
                            </div>
                        </div>
                        <div class="col-12 text-end">
                            <button type="submit" class="btn btn-success rounded-pill px-4 py-2">Обновить цену</button>
                        </div>
                    </form>
                </article>
                <?php
                require('pages/editpage/producteditimage.php');
                ?>
                <article class="bg-white border rounded-4 p-4 p-sm-5 shadow-sm mb-4">
                    <div class="d-flex align-items-center gap-3 mb-4">
                        <div class="bg-warning-subtle text-warning rounded-3 p-3 d-flex align-items-center justify-content-center"
                            style="width: 48px; height: 48px;">
                            <i class="bi bi-layers fs-4"></i>
                        </div>
                        <div>
                            <h2 class="h5 mb-0 fw-bold text-dark">Текущие варианты на складе</h2>
                            <p class="text-secondary small mb-0">Управление цветами, размерами и остатками</p>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Цвет</th>
                                    <th>Размер</th>
                                    <th>Запас</th>
                                    <th class="text-end">Действия</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($variants as $variant): ?>
                                    <tr>
                                        <form method="post" action="/lib/api/private/update_product.php">
                                            <input type="hidden" name="action_type" value="edit_variant">
                                            <input type="hidden" name="productid" value="<?= $details->id ?>">
                                            <input type="hidden" name="variantid" value="<?= $variant->id ?>">

                                            <td>
                                                <div class="d-flex align-items-center gap-2">
                                                    <input type="color" name="hex"
                                                        class="form-control form-control-color border-0"
                                                        value="<?= htmlspecialchars($variant->hex) ?>"
                                                        title="Выберите цвет">
                                                    <span
                                                        class="small fw-semibold"><?= htmlspecialchars($variant->color) ?></span>
                                                </div>
                                            </td>
                                            <td>
                                                <input type="text" name="size"
                                                    class="form-control form-control-sm rounded-3 fw-bold"
                                                    value="<?= htmlspecialchars($variant->size) ?>" style="width: 70px;">
                                            </td>
                                            <td>
                                                <input type="number" name="quantity"
                                                    class="form-control form-control-sm rounded-3"
                                                    value="<?= htmlspecialchars($variant->stock) ?>" style="width: 90px;">
                                            </td>
                                            <td class="text-end">
                                                <button type="submit" name="action" value="change"
                                                    class="btn btn-sm btn-outline-primary rounded-circle" title="Сохранить">
                                                    <i class="bi bi-check-lg"></i>
                                                </button>
                                                <button type="submit" name="action" value="remove"
                                                    class="btn btn-sm btn-outline-danger rounded-circle ms-1"
                                                    title="Удалить" onclick="return confirm('Удалить данный вариант?')">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </td>
                                        </form>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </article>

                <article class="bg-white border rounded-4 p-4 p-sm-5 shadow-sm">
                    <div class="d-flex align-items-center justify-content-between mb-4">
                        <div class="d-flex align-items-center gap-3">
                            <div class="bg-info-subtle text-info rounded-3 p-3 d-flex align-items-center justify-content-center"
                                style="width: 48px; height: 48px;">
                                <i class="bi bi-plus-circle fs-4"></i>
                            </div>
                            <div>
                                <h2 class="h5 mb-0 fw-bold text-dark">Добавить новые варианты</h2>
                                <p class="text-secondary small mb-0">Создание новых комбинаций размера и цвета</p>
                            </div>
                        </div>
                    </div>

                    <form method="post" action="/lib/api/private/update_product.php" id="addVariantsForm">
                        <input type="hidden" name="action_type" value="add_new_variants">
                        <input type="hidden" name="productid" value="<?= $details->id ?>">
                        <input type="hidden" name="new_products" id="productsInput">

                        <div id="newVariantsContainer" class="d-flex flex-column gap-3 mb-4">
                            <!-- JS будет динамически добавлять сюда строки -->
                        </div>

                        <div class="d-flex justify-content-between align-items-center">
                            <button type="button" class="btn btn-outline-secondary rounded-pill" id="addVariantRowBtn">
                                <i class="bi bi-plus-lg me-1"></i> Добавить строку
                            </button>
                            <button type="submit" class="btn btn-dark rounded-pill px-4">
                                Сохранить новые варианты
                            </button>
                        </div>
                    </form>
                </article>

            </div>

            <div class="col-12 col-lg-4">
                <article class="bg-white border rounded-4 p-4 shadow-sm sticky-top" style="top: 100px;">
                    <h3 class="h6 font-bold text-dark mb-3">Информация о товаре</h3>
                    <div class="d-flex flex-column gap-2 text-secondary small">
                        <div class="d-flex justify-content-between border-bottom pb-2">
                            <span>ID Товара:</span>
                            <span class="fw-bold text-dark">#<?= $details->id ?></span>
                        </div>
                        <div class="d-flex justify-content-between border-bottom pb-2">
                            <span>Всего вариантов:</span>
                            <span class="fw-bold text-dark"><?= count($variants) ?></span>
                        </div>
                    </div>
                </article>
            </div>

        </div>
    </main>

    <footer class="bg-white border-top py-3 mt-5">
        <div class="container-fluid px-4 text-center text-secondary small">
            &copy; <?= date('Y') ?> Admin Panel. Все права защищены.
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const container = document.getElementById('newVariantsContainer');
            const addBtn = document.getElementById('addVariantRowBtn');
            const form = document.getElementById('addVariantsForm');
            const hiddenInput = document.getElementById('productsInput');

            function createRow() {
                const row = document.createElement('div');
                row.className = 'row g-3 align-items-center variant-row border p-3 rounded-4 bg-light-subtle';
                row.innerHTML = `
                    <div class="col-12 col-sm-4">
                        <label class="form-label small fw-semibold text-secondary mb-1">Цвет</label>
                        <input type="color" class="form-control form-control-color w-100 variant-color" value="#000000">
                    </div>
                    <div class="col-12 col-sm-3">
                        <label class="form-label small fw-semibold text-secondary mb-1">Размер</label>
                        <input type="text" class="form-control rounded-3 variant-size" placeholder="S, M, L..." required>
                    </div>
                    <div class="col-12 col-sm-3">
                        <label class="form-label small fw-semibold text-secondary mb-1">Количество</label>
                        <input type="number" class="form-control rounded-3 variant-qty" value="1" min="0" required>
                    </div>
                    <div class="col-12 col-sm-2 text-end">
                        <label class="form-label d-none d-sm-block">&nbsp;</label>
                        <button type="button" class="btn btn-outline-danger rounded-circle remove-row-btn">
                            <i class="bi bi-x-lg"></i>
                        </button>
                    </div>
                `;

                row.querySelector('.remove-row-btn').addEventListener('click', () => {
                    row.remove();
                });

                container.appendChild(row);
            }

            addBtn.addEventListener('click', createRow);

            form.addEventListener('submit', (e) => {
                const rows = container.querySelectorAll('.variant-row');
                const variantsData = [];

                rows.forEach(row => {
                    const color = row.querySelector('.variant-color').value;
                    const size = row.querySelector('.variant-size').value.trim();
                    const quantity = row.querySelector('.variant-qty').value;

                    if (size) {
                        variantsData.push({ color, size, quantity });
                    }
                });

                hiddenInput.value = JSON.stringify(variantsData);
            });
        });
    </script>
</body>

</html>