<?php

declare(strict_types=1);

session_start();

require_once('../../db.php');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    exit('Необходимо авторизоваться');
}

$userId = (int) $_SESSION['user_id'];

$sql = 'SELECT role FROM users WHERE id = ? LIMIT 1';

$stmt = $pdo->prepare($sql);
$stmt->execute([$userId]);

$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user || $user['role'] !== 'admin') {
    http_response_code(403);
    exit('Доступ запрещён');
}


$image_id = (int) ($_POST['image_id'] ?? 0);
$is_main = (int) ($_POST['ismain'] ?? 1);
$image_name = ($_POST['image_name'] ?? 0);
$referer = $_SERVER['HTTP_REFERER'] ?? '';
if (!empty($referer)) {
    $queryString = parse_url($referer, PHP_URL_QUERY);
    if ($queryString) {
        parse_str($queryString, $queryParams);
        $product_id = isset($queryParams['id']) ? (int) $queryParams['id'] : null;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($_POST['action'] === 'change') {
        if (!isset($_FILES['photo'])) {
            die('Выберите фото');
        }

        if ($_FILES['photo']['error'] !== UPLOAD_ERR_OK) {
            die('Ошибка загрузки: ' . $_FILES['photo']['error']);
        }

        $uploadDir = __DIR__ . '/upload/';

        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $fileName = $_FILES['photo']['name'];
        $filePath = $uploadDir . $fileName;
        $filePathRemove = __DIR__ . '/upload/' . $image_name;

        if (move_uploaded_file($_FILES['photo']['tmp_name'], $filePath)) {
            $sql = "UPDATE product_images SET image_url = ? WHERE id = ?";
            $query = $pdo->prepare($sql);
            $query->execute([$fileName, $image_id]);

            if (file_exists($filePathRemove)) {
                if (unlink($filePathRemove)) {
                    header("Location: ../../../productedit.php?id={$product_id}&success=1");
                } else {
                    echo 'Ошибка удаления';
                }
            } else {
                echo 'Файл не найден: ' . $filePath;
            }
        } else {
            echo "Ошибка при сохранении файла";
        }
    }

    if ($_POST['action'] === 'remove') {
        try {
            $sql = "DELETE FROM product_images WHERE id = ?";
            $query = $pdo->prepare($sql);
            $query->execute([$image_id]);
            $filePath = __DIR__ . '/upload/' . $image_name;

            if (file_exists($filePath)) {
                if (unlink($filePath)) {
                    header("Location: ../../../productedit.php?id={$product_id}&success=1");
                } else {
                    echo 'Ошибка удаления';
                }
            } else {
                echo 'Файл не найден: ' . $filePath;
            }
        } catch (\Throwable $th) {
            throw $th;

        }
    }
    if ($_POST['action'] === 'add') {
        
        $sql = 'SELECT * FROM product_images WHERE product_id = ? AND is_main = 1';
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$product_id]);
        $productmain = $stmt->fetch(PDO::FETCH_ASSOC);
        $setmain = 0;
        if (!$productmain) {
            $setmain = 1;
        };
        try {
            if (!isset($_FILES['photo'])) {
                die('Выберите фото');
            }

            if ($_FILES['photo']['error'] !== UPLOAD_ERR_OK) {
                die('Ошибка загрузки: ' . $_FILES['photo']['error']);
            }

            $uploadDir = __DIR__ . '/upload/';

            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            $fileName = $_FILES['photo']['name'];
            $filePath = $uploadDir . $fileName;
            if (move_uploaded_file($_FILES['photo']['tmp_name'], $filePath)) {
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
                $imageStmt->execute([
                    $product_id,
                    $fileName,
                    $setmain
                ]);
                header("Location: ../../../productedit.php?id={$product_id}&success=1");
            } else {
                echo "Ошибка при сохранении файла";
            }
        } catch (PDOException $e) {
            if ($e->getCode() == '45000') {
                echo "Ошибка: У этого продукта уже загружено максимум 4 фотографии.";
            } else {
                echo "Произошла системная ошибка при сохранении в базу данных.";
                error_log($e->getMessage());
            }

        }
    }
}
