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

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../index.php');
    exit;
}
$product_id = (int) ($_POST['productid'] ?? $_POST['product_id'] ?? 0);
$action_type = $_POST['action_type'] ?? 'all';
function hexToColorName(string $hex): ?string
{
    static $cache = [];
    $hex = strtoupper(ltrim(trim($hex), '#'));
    if (!preg_match('/^[0-9A-F]{6}$/', $hex))
        return null;
    if (isset($cache[$hex]))
        return $cache[$hex];
    $url = 'https://api.color.pizza/v1/' . $hex;
    $context = stream_context_create([
        'http' => [
            'timeout' => 3,
            'ignore_errors' => true,
            'header' => "Accept: application/json\r\n"
        ]
    ]);

    $response = @file_get_contents($url, false, $context);
    if ($response !== false) {
        $data = json_decode($response, true);
        if (isset($data['colors'][0]['name'])) {
            $cache[$hex] = $data['colors'][0]['name'];
            return $cache[$hex];
        }
    }
    return 'Неизвестный цвет';
}

try {
    $pdo->beginTransaction();
    if (in_array($action_type, ['all', 'edit_details']) && isset($_POST['title'])) {
        $title = trim($_POST['title'] ?? '');
        $category = trim($_POST['category'] ?? '');
        $desc = trim($_POST['desc'] ?? '');

        $sql = "UPDATE products SET name = ?, description = ?, category = ? WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$title, $desc, $category, $product_id]);
    }

    if (in_array($action_type, ['all', 'edit_price']) && isset($_POST['price'])) {
        $price = (int) ($_POST['price'] ?? 0);
        $discount = (int) ($_POST['discount'] ?? 0);

        $sql = "UPDATE products SET price = ?, discount = ? WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$price, $discount, $product_id]);
    }

    if (in_array($action_type, ['all', 'edit_variant'])) {
        if (isset($_POST['action']) && $_POST['action'] === 'remove') {
            $variant_id = (int) ($_POST['variantid'] ?? 0);
            $stmt = $pdo->prepare("DELETE FROM product_variants WHERE id = ?");
            $stmt->execute([$variant_id]);
        } elseif (isset($_POST['action']) && $_POST['action'] === 'change') {
            $variant_id = (int) ($_POST['variantid'] ?? 0);
            $quantity = (int) ($_POST['quantity'] ?? 0);
            $size = trim($_POST['size'] ?? '');
            $hex = trim($_POST['hex'] ?? '');
            $colorName = hexToColorName($hex);

            $sql = "UPDATE product_variants SET hex = ?, color = ?, size = ?, stock = ? WHERE id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$hex, $colorName, $size, $quantity, $variant_id]);
        }

        if (isset($_POST['existing_variants']) && is_array($_POST['existing_variants'])) {
            foreach ($_POST['existing_variants'] as $v_id => $v_data) {
                $hex = trim($v_data['hex']);
                $size = trim($v_data['size']);
                $quantity = (int) $v_data['quantity'];
                $colorName = hexToColorName($hex);

                $sql = "UPDATE product_variants SET hex = ?, color = ?, size = ?, stock = ? WHERE id = ?";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$hex, $colorName, $size, $quantity, $v_id]);
            }
        }
    }
    if (!empty($_POST['new_products']) || !empty($_POST['products'])) {
        $json_data = $_POST['new_products'] ?? $_POST['products'];
        $new_variants = json_decode($json_data, true);

        if (is_array($new_variants)) {
            $stmt = $pdo->prepare("INSERT INTO product_variants (product_id, hex, color, size, stock) VALUES (?, ?, ?, ?, ?)");
            foreach ($new_variants as $v) {
                $hex = trim($v['color'] ?? '#000000');
                $size = trim($v['size'] ?? 'M');
                $quantity = (int) ($v['quantity'] ?? 0);
                $colorName = hexToColorName($hex);

                $stmt->execute([$product_id, $hex, $colorName, $size, $quantity]);
            }
        }
    }

    $pdo->commit();
    header("Location: ../../../productedit.php?id={$product_id}&success=1");
    exit;

} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo "Ошибка при сохранении: " . $e->getMessage();
}