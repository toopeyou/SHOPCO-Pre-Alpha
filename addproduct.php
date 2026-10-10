<?php

session_start();
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}   

require __DIR__ . '/lib/db.php';
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



?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Создание продукта</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.0/css/all.min.css">
</head>
<body>
    <main class="container py-4 my-2">
        <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-4">
            <div>
                <h1 class="h2 fw-bold text-uppercase mb-1">Добавление нового продукта</h1>
                <p class="text-secondary mb-0">Заполните данные для публикации нового товара в онлайн-витрине Shop.co</p>
            </div>
        </div>
        <form action="/lib/api/private/createproduct.php" method="POST" enctype="multipart/form-data" id="addProductForm">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">

            <div class="row g-4">
                <div class="col-lg-5">
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-body p-4">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h5 class="card-title fw-bold mb-0">Фотографии товара</h5>
                                <span class="badge bg-light text-dark border rounded-pill px-3 py-2">Всего 4 фото</span>
                            </div>
                            <div class="mb-4">
                                <label class="form-label fw-semibold d-flex justify-content-between">
                                    <span>1. Основное фото (Главная обложка)</span>
                                    <span class="text-danger fw-bold">*Обязательно</span>
                                </label>
                                <label
                                    class="border border-2 border-secondary-subtle rounded-4 bg-light d-flex flex-column justify-content-center align-items-center text-center p-4"
                                    style="min-height: 280px; cursor: pointer;">
                                    <div class="bg-white rounded-circle shadow-sm d-flex align-items-center justify-content-center mb-3" style="width: 68px; height: 68px;">
                                        <img id="preview" src="" alt="Предпросмотр" style="max-width: 68px; display: none;">
                                    </div>
                                    <p class="text-secondary small mb-3">PNG, JPG, WEBP <br>рекомендуется 1000×1200px </p>
                                    <span class="btn btn-dark btn-sm rounded-pill px-4 py-2 fw-semibold">
                                        <i class="bi bi-upload me-1"></i>Выбрать файл
                                    </span>
                                    <input id="fileİnput" name="photo" type="file" accept="image/*" class="d-none" required>
                                </label>
                            </div>
                            <div>
                                <label class="form-label fw-semibold d-flex justify-content-between">
                                    <span>2. Дополнительные ракурсы</span>
                                    <span class="text-secondary fw-normal">До 3 фото</span>
                                </label>
                                <p class="text-secondary small mb-2">Миниатюры для галереи на странице товара</p>
                                <div class="row g-2">
                                    <div class="col-4">
                                        <label class="border border-2 border-secondary-subtle rounded-3 bg-light d-flex flex-column align-items-center justify-content-center text-center p-3 h-100"  style="min-height: 120px; cursor: pointer;">
                                            <i class="bi bi-camera text-secondary fs-4 mb-1"></i>
                                            <span class="small fw-semibold text-dark">Фото 2</span>
                                            <span class="text-secondary" style="font-size: 11px;"> Сзади</span>
                                            <input name="photo1" type="file" accept="image/*" class="d-none">
                                        </label>
                                    </div>
                                    <div class="col-4">
                                        <label class="border border-2 border-secondary-subtle rounded-3 bg-light d-flex flex-column align-items-center justify-content-center text-center p-3 h-100" style="min-height: 120px; cursor: pointer;">
                                            <i class="bi bi-camera text-secondary fs-4 mb-1"></i>
                                            <span class="small fw-semibold text-dark"> Фото 3</span>
                                            <span class="text-secondary" style="font-size: 11px;">На модели</span>
                                            <input name="photo2" type="file" accept="image/*" class="d-none">
                                        </label>
                                    </div>
                                    <div class="col-4">
                                        <label class="border border-2 border-secondary-subtle rounded-3 bg-light d-flex flex-column align-items-center justify-content-center text-center p-3 h-100" style="min-height: 120px; cursor: pointer;">
                                            <i class="bi bi-camera text-secondary fs-4 mb-1"></i>
                                            <span class="small fw-semibold text-dark"> Фото 4</span>
                                            <span class="text-secondary" style="font-size: 11px;">
                                                Детали/Ткань
                                            </span>

                                            <input name="photo3" type="file" accept="image/*" class="d-none">
                                        </label>
                                    </div>

                                </div>
                            </div>
                            <div class="alert alert-light border rounded-3 d-flex gap-2 align-items-start mt-3 mb-0">
                                <i class="bi bi-info-circle text-primary fs-5"></i>
                                <div class="small text-secondary">
                                    Качественные снимки повышают конверсию на 35%.
                                    Используйте светлый нейтральный фон в стиле Shop.co.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-7">
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-body p-4">
                            <h5 class="fw-bold mb-3">Основная информация</h5>
                            <div class="mb-3">
                                <label for="productTitle" class="form-label fw-semibold">Название товара<span class="text-danger">*</span></label>
                                <input type="text" name="productTitle" id="productTitle" class="form-control rounded-3" placeholder="Например: ONE LIFE GRAPHIC T-SHIRT" value="ONE LIFE GRAPHIC T-SHIRT" required>
                            </div>
                            <div class="row g-3 mb-3">
                                <div class="col-md-6">
                                    <label for="productCategory" class="form-label fw-semibold">Категория</label>
                                    <select name="productCategory" id="productCategory" class="form-select rounded-3">
                                        <option selected>T-Shirts</option>
                                        <option>Shirts</option>
                                        <option>Jeans</option>
                                        <option>Shorts</option>
                                        <option>Hoodie</option>
                                    </select>
                                </div>
                            </div>
                            <div>
                                <label for="productDescription" class="form-label fw-semibold">Описание товара <span class="text-danger">*</span></label>
                                <textarea required id="productDescription" name="productDescription" class="form-control rounded-3" rows="4"
                                    placeholder="Подробное описание ткани, посадки, ухода и стиля...">
                                </textarea>
                            </div>
                        </div>
                    </div>
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-body p-4">
                            <h5 class="fw-bold mb-3"> Ценообразование и Склад</h5>
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label for="basePrice" class="form-label fw-semibold">Базовая цена ($)
                                        <span class="text-danger">*</span>
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-white fw-bold">₽</span>
                                        <input type="number" name="basePrice" id="basePrice" class="form-control" placeholder="₽" value="" required>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <label for="discountRate" class="form-label fw-semibold">Скидка (%)</label>
                                    <div class="input-group">
                                        <input onchange="" type="number" name="discount" id="discountRate" class="form-control" placeholder="%" value="">
                                        <span class="input-group-text bg-white fw-bold">%</span>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">Итоговая цена</label>
                                    <div class="form-control bg-light d-flex align-items-center justify-content-between">
                                        <span class="finalprice fw-bold"></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card shadow-sm mb-4">
                        <div class="card-body p-4">
                            <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
                                <div>
                                    <h5 class="fw-bold mb-1">Варианты товара</h5>
                                    <p class="text-muted small mb-0">Цвет, размер и количество на складе для таблицы</p>
                                </div>
                            </div>
                            <input type="hidden" name="products" id="products">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th scope="col" class="small text-uppercase text-secondary">Цвет</th>
                                            <th scope="col" class="small text-uppercase text-secondary">Размер</th>
                                            <th scope="col" class="small text-uppercase text-secondary">Остаток</th>
                                        </tr>
                                    </thead>
                                    <tbody id="tbody" class="tbody">
                                    </tbody>
                                </table>
                            </div>
                            <div class="border-top mt-3 pt-3">
                                <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
                                    <div class="mt-5 d-flex align-items-center gap-2 text-secondary small">
                                        <table class="table table-hover align-middle mb-0">
                                            <tbody>
                                                <tr>
                                                    <td>
                                                        <div class="d-flex align-items-center gap-2">
                                                            <input type="color" id="color" class="form-control form-control-color border-0 p-0" value="#4F563B" title="Оливковый">
                                                            <div>
                                                                <div class="fw-semibold small">Оливковый</div>
                                                                <div class="text-muted small">#4F563B</div>
                                                            </div>
                                                        </div>
                                                    </td>

                                                    <td>
                                                        <select id="size"
                                                            class="form-select form-select-sm fw-semibold">
                                                            <option>S</option>
                                                            <option>M</option>
                                                            <option selected>L</option>
                                                            <option>XL</option>
                                                            <option>XXL</option>
                                                        </select>
                                                    </td>
                                                    <td>
                                                        <div class="input-group input-group-sm">
                                                            <input type="number" id="quantity" class="form-control text-center fw-bold" value="50"  min="0">
                                                            <span class="input-group-text bg-white">шт.</span>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <input type="text" class="form-control form-control-sm text-secondary" value="SHP-TS-0091-OLV-L" readonly>
                                                    </td>
                                                    <td class="text-end">
                                                        <button id="add" type="button"class="btn btn-outline-primary btn-sm d-inline-flex align-items-center gap-2">
                                                            <i class="bi bi-plus-lg"></i>
                                                            Добавить вариант
                                                        </button>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="d-flex flex-column flex-sm-row align-items-stretch align-items-sm-center justify-content-end gap-2 pt-2">
                        <button type="button" class="btn btn-outline-dark rounded-pill px-4 py-2 fw-semibold">Сохранить черновик</button>
                        <button type="submit" class="btn btn-dark rounded-pill px-4 py-2 fw-bold"><i class="bi bi-check2-circle me-2"></i>Опубликовать</button>
                    </div>
                </div>
            </div>
        </form>
    </main>
    <script>
        const finalprice = document.querySelector('.finalprice');
        const discountRate = document.getElementById('discountRate');
        const price = document.getElementById('basePrice');

        function calculateFinalPrice() {
            const basePrice = parseFloat(price.value) || 0;
            const discount = parseFloat(discountRate.value) || 0;

            const final = basePrice - (basePrice * discount / 100);

            finalprice.textContent = final.toFixed(2);
        }
        price.addEventListener('input', calculateFinalPrice);
        discountRate.addEventListener('input', calculateFinalPrice);


        const input = document.getElementById('fileİnput');
        const preview = document.getElementById('preview');

        input.addEventListener('change', function () {
            const file = this.files[0];

            if (file) {
                preview.src = URL.createObjectURL(file);
                preview.style.display = 'block';
            }
        });
        const addProductColor = document.getElementById('color')
        const addProductSize = document.getElementById('size')
        const addProductQuantity = document.getElementById('quantity')
        const tbody = document.getElementById('tbody')
        const products = [];

        const addProductBtn = document.getElementById('add')

        addProductBtn.onclick = function () {
            const product = {
                color: addProductColor.value,
                size: addProductSize.value,
                quantity: addProductQuantity.value
            };
            products.push(product);
            const longHTML = `
                    <tr>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <input type="color"
                                    class="form-control form-control-color border-0 p-0"
                                    value="${product.color}" title="Оливковый" disabled>

                                <div>
                                    <div class="fw-semibold small">
                                        Оливковый
                                    </div>
                                    <div class="text-muted small">
                                    ${product.color}
                                    </div>
                                </div>
                            </div>
                        </td>

                        <td>
                            <span class="form-select-sm fw-semibold">
                                ${product.size}
                            </span>
                        </td>

                        <td>
                            <div class="input-group input-group-sm">
                                <input type="number" class="form-control text-center fw-bold"
                                    value="${product.quantity}" disabled >
                                <span class="input-group-text bg-white">
                                    шт.
                                </span>
                            </div>
                        </td>

                        <td>
                            <input type="text" class="form-control form-control-sm text-secondary"
                                value="SHP-TS-0091-OLV-L" readonly>
                        </td>

                        <td class="text-end">
                            <button type="button" class="btn btn-sm btn-outline-danger"
                                title="Удалить вариант">
                                <i class="bi bi-trash"></i>
                            </button>
                        </td>
                    </tr>

                `;
            tbody.insertAdjacentHTML('beforeend', longHTML);
        };


        document.getElementById('addProductForm').addEventListener('submit', function () {
            document.getElementById('products').value = JSON.stringify(products);

            console.log(document.getElementById('products').value);
        });


    </script>
</body>


</html>