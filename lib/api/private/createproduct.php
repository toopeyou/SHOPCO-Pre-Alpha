<?php

declare(strict_types=1);

session_start();

if (
    empty($_POST['csrf_token']) ||
    empty($_SESSION['csrf_token']) ||
    !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])
) {
    http_response_code(403);
    exit('Неверный CSRF-токен');
}
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    exit('Необходимо авторизоваться');
}
require_once('../../db.php');
$userId = (int) $_SESSION['user_id'];


$sql = 'SELECT role FROM users WHERE id = ? LIMIT 1';
$stmt = $pdo->prepare($sql);
$stmt->execute([$userId]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user || $user['role'] !== 'admin') {
    http_response_code(403);
    exit('Доступ запрещён');
}
$uploadDir = __DIR__ . '/upload/';
$uploadedFiles = [];

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new RuntimeException('Разрешён только POST-запрос');
    }
    $title = trim($_POST['productTitle'] ?? '');
    $category = trim($_POST['productCategory'] ?? '');
    $description = trim($_POST['productDescription'] ?? '');
    $basePrice = $_POST['basePrice'] ?? '';
    $discount = $_POST['discount'] ?? '';
    $productsJson = $_POST['products'] ?? '';
    if ($title === '') {
        throw new RuntimeException('Не указано название товара');
    }
    if ($category === '') {
        throw new RuntimeException('Не указана категория товара');
    }
    if ($basePrice === '' || !is_numeric($basePrice)) {
        throw new RuntimeException('Некорректная цена');
    }
    $basePrice = (float) $basePrice;
    if ($basePrice < 0) {
        throw new RuntimeException('Цена не может быть отрицательной');
    }
    if ($discount === '') {
        $discount = 0;
    }
    if (!is_numeric($discount)) {
        throw new RuntimeException('Некорректная скидка');
    }

    $discount = (float) $discount;

    if ($discount < 0 || $discount > 100) {
        throw new RuntimeException('Скидка должна быть от 0 до 100%');
    }
    if ($productsJson === '') {
        throw new RuntimeException('Варианты товара не переданы');
    }

    try {
        $products = json_decode(
            $productsJson,
            true,
            512,
            JSON_THROW_ON_ERROR
        );

    } catch (JsonException $e) {
        throw new RuntimeException('Ошибка JSON: ' . $e->getMessage());
    }

    if (!is_array($products) || count($products) === 0) {
        throw new RuntimeException('Добавьте хотя бы один вариант товара');
    }
    $variants = [];
    foreach ($products as $index => $product) {
        $variantNumber = $index + 1;
        if (!is_array($product)) {
            throw new RuntimeException("Вариант №{$variantNumber} имеет неправильный формат");
        }

        $hex = trim((string) ($product['color'] ?? ''));
        $size = trim((string) ($product['size'] ?? ''));
        $quantity = $product['quantity'] ?? null;

        if (!preg_match('/^#[0-9A-Fa-f]{6}$/', $hex)) {
            throw new RuntimeException("Некорректный HEX цвета в варианте №{$variantNumber}");
        }
        if ($size === '') {
            throw new RuntimeException("Не указан размер в варианте №{$variantNumber}");
        }
        if (
            $quantity === null ||
            filter_var(
                $quantity,
                FILTER_VALIDATE_INT,
                [
                    'options' => [
                        'min_range' => 0
                    ]
                ]
            ) === false
        ) {
            throw new RuntimeException("Некорректное количество в варианте №{$variantNumber}");
        }

        $quantity = (int) $quantity;

        $colorName = hexToColorName($hex);

        if ($colorName === null) {
            throw new RuntimeException("Не удалось определить цвет {$hex}");
        }

        $variants[] = [
            'color' => $colorName,
            'size' => $size,
            'stock' => $quantity,
            'hex' => strtoupper($hex),
        ];
    }
    if (
        !isset($_FILES['photo']) ||
        !is_array($_FILES['photo'])
    ) {
        throw new RuntimeException('Главное фото не выбрано');
    }


    if (!is_dir($uploadDir)) {
        if (!mkdir($uploadDir, 0755, true)) {
            throw new RuntimeException('Не удалось создать папку upload');
        }
    }

    if (!is_writable($uploadDir)) {
        throw new RuntimeException('Папка upload недоступна для записи');
    }

    $mainImage = uploadImage(
        $_FILES['photo'],
        $uploadDir
    );
    $uploadedFiles[] = $mainImage;

    $additionalPhotos = [
        'photo1',
        'photo2',
        'photo3'
    ];
    
    foreach ($additionalPhotos as $photoName) {
        if (!isset($_FILES[$photoName])) { continue;}
        if ($_FILES[$photoName]['error'] === UPLOAD_ERR_NO_FILE) {continue;}
        $image = uploadImage(
            $_FILES[$photoName],
            $uploadDir
        );
        $uploadedFiles[] = $image;
    }

    $pdo->beginTransaction();

    $sql = "
        INSERT INTO products
        (
            name,
            description,
            price,
            discount,
            rate,
            gender,
            category
        )
        VALUES
        (?, ?, ?, ?, ?, ?, ?)
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        $title,
        $description,
        $basePrice,
        $discount,
        0,
        'unisex',
        $category
    ]);
    $lastId = (int) $pdo->lastInsertId();
    if ($lastId <= 0) {
        throw new RuntimeException('Не удалось получить ID созданного товара');
    }



    $imageSql = "
        INSERT INTO product_images
        (
            product_id,
            image_url,
            is_main
        )
        VALUES (?, ?, ?)
    ";

    $imageStmt = $pdo->prepare($imageSql);
    foreach ($uploadedFiles as $index => $image) {
        $imageStmt->execute([
            $lastId,
            $image,
            $index === 0 ? 1 : 0
        ]);
    }

    $variantSql = "
        INSERT INTO product_variants
        (
            product_id,
            color,
            size,
            stock,
            hex
        )
        VALUES (?, ?, ?, ?, ?)
    ";

    $variantStmt = $pdo->prepare($variantSql);
    foreach ($variants as $variant) {
        $variantStmt->execute([
            $lastId,
            $variant['color'],
            $variant['size'],
            $variant['stock'],
            $variant['hex']
        ]);
    }
    
    $pdo->commit();

    header('Location: ../../../addproduct.php');
    echo '
        <h3>Товар успешно создан</h3>
        <p>ID товара: ' . htmlspecialchars((string) $lastId) . '</p>
    ';
    header('');
} catch (Throwable $e) {
    if (
        isset($pdo) &&
        $pdo instanceof PDO &&
        $pdo->inTransaction()
    ) {$pdo->rollBack();}

    foreach ($uploadedFiles as $file) {
        $path = $uploadDir . $file;
        if (is_file($path)) {
            @unlink($path);
        }
    }
    http_response_code(400);
    echo '
        <h3>Ошибка</h3>
        <p>' .
        htmlspecialchars(
            $e->getMessage(),
            ENT_QUOTES,
            'UTF-8'
        )
        . '</p>
    ';
}




function uploadImage(
    array $file,
    string $uploadDir
): string {
    if (
        !isset($file['error']) ||
        $file['error'] !== UPLOAD_ERR_OK
    ) {
        $error = $file['error'] ?? -1;
        throw new RuntimeException('Ошибка загрузки изображения. Код: ' . $error);
    }
    $maxSize = 10 * 1024 * 1024;

    if (($file['size'] ?? 0) > $maxSize) {
        throw new RuntimeException('Размер изображения не должен превышать 10 MB');
    }
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($file['tmp_name']);

    $allowedTypes = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
        'image/gif' => 'gif'
    ];

    if (!isset($allowedTypes[$mime])) {
        throw new RuntimeException(
            'Разрешены только JPG, PNG, WEBP и GIF'
        );
    }

    if (@getimagesize($file['tmp_name']) === false) {
        throw new RuntimeException(
            'Файл не является корректным изображением'
        );
    }

    $extension = $allowedTypes[$mime];
    $fileName = bin2hex(random_bytes(16)) . '.' . $extension;
    $destination = $uploadDir . $fileName;

    if (
        !move_uploaded_file(
            $file['tmp_name'],
            $destination
        )
    ) {
        throw new RuntimeException('Не удалось сохранить изображение');
    }
    return $fileName;
}


function hexToColorName(string $hex): ?string
{
    static $cache = [];

    $hex = strtoupper(ltrim(trim($hex), '#'));

    if (!preg_match('/^[0-9A-F]{6}$/', $hex)) {return null;}

    if (isset($cache[$hex])) {return $cache[$hex];}

    $url = 'https://api.color.pizza/v1/' . $hex;
    $context = stream_context_create([
        'http' => [
            'timeout' => 3,
            'ignore_errors' => true,
            'header' => "Accept: application/json\r\n"
        ]
    ]);
    $response = @file_get_contents(
        $url,
        false,
        $context
    );
    if ($response === false) {return null;}
    try {
        $data = json_decode(
            $response,
            true,
            512,
            JSON_THROW_ON_ERROR
        );
    } catch (JsonException $e) {
        return null;
    }
    if (
        !isset($data['colors'][0]['name']) ||
        !is_string($data['colors'][0]['name'])
    ) {
        return null;
    }
    $name = $data['colors'][0]['name'];
    $translations = [
        'Red' => 'Красный',
        'Green' => 'Зелёный',
        'Blue' => 'Синий',
        'Yellow' => 'Жёлтый',
        'Orange' => 'Оранжевый',
        'Purple' => 'Фиолетовый',
        'Pink' => 'Розовый',
        'Brown' => 'Коричневый',
        'Black' => 'Чёрный',
        'White' => 'Белый',
        'Gray' => 'Серый',
        'Grey' => 'Серый',
        'Cyan' => 'Бирюзовый',
        'Magenta' => 'Пурпурный',
        'Navy' => 'Тёмно-синий',
        'Olive' => 'Оливковый',
        'Lime' => 'Лаймовый',
        'Teal' => 'Бирюзовый',
        'Maroon' => 'Бордовый',
        'Gold' => 'Золотой',
        'Silver' => 'Серебряный'
    ];
    $result = $translations[$name] ?? $name;
    $cache[$hex] = $result;
    return $result;
}