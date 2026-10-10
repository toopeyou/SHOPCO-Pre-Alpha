<?php

session_start();
if (
    empty($_POST['csrf_token']) ||
    empty($_SESSION['csrf_token']) ||
    !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])
) {
    http_response_code(403);
    exit('Неверный CSRF-токен');
}

require_once __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/lib/db.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();
use YooKassa\Client;

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    exit('Необходимо авторизоваться');
}

$userId = (int) $_SESSION['user_id'];

$sql = "
    SELECT
        ci.user_id,
        ci.variant_id,
        ci.quantity,

        u.email AS user_email,

        pv.product_id,
        pv.color,
        pv.size,
        pv.stock,
        pv.sku,
        pv.hex,

        p.id,
        p.name,
        p.description,
        p.price,
        p.discount,
        p.rate,
        p.gender,
        p.category

    FROM cart_items AS ci

    INNER JOIN users AS u
        ON u.id = ci.user_id

    INNER JOIN product_variants AS pv
        ON pv.id = ci.variant_id

    INNER JOIN products AS p
        ON p.id = pv.product_id

    WHERE ci.user_id = ?
";

$query = $pdo->prepare($sql);
$query->execute([$userId]);
$cartItems = $query->fetchAll(PDO::FETCH_OBJ);

if (!$cartItems) {
    http_response_code(400);
    exit('Корзина пуста');
}


$finalPrice = 0;
foreach ($cartItems as $item) {

    $price = (float) $item->price;
    $discount = (float) $item->discount;
    $quantity = (int) $item->quantity;
    $stock = (int) $item->stock;


  
    if ($quantity <= 0) {
        http_response_code(400);
        exit('Некорректное количество товара');
    }

    if ($stock < $quantity) {
        http_response_code(400);
        exit(
            'Недостаточно товара: ' .
            htmlspecialchars($item->name)
        );
    }


    $unitPrice = $price * (1 - $discount / 100);

    $itemTotal = $unitPrice * $quantity;
    $finalPrice += $itemTotal;
}


$finalPrice = round($finalPrice, 2);


if ($finalPrice <= 0) {
    http_response_code(400);
    exit('Некорректная сумма заказа');
}

$action = ($_POST['buy'] ?? null);


$pdo->beginTransaction();

try {
    if ($action) {
        try {
            $title = trim($_POST['title'] ?? '');
            $recipientName = trim($_POST['recipient_name'] ?? '');
            $recipientPhone = trim($_POST['recipient_phone'] ?? '');
            $address = trim($_POST['address'] ?? '');
            $apartment = trim($_POST['apartment'] ?? '');
            $entrance = trim($_POST['entrance'] ?? '');
            $floor = trim($_POST['floor'] ?? '');
            $city = trim($_POST['city'] ?? '');
            $postcode = trim($_POST['postcode'] ?? '');
            $comment = trim($_POST['comment'] ?? '');
            $isDefault = isset($_POST['is_default']) ? 1 : 0;
            if (
                $recipientName === '' ||
                $recipientPhone === '' ||
                $address === '' ||
                $city === ''
            ) {
                header('Location: /addresses.php?error=required');
                exit;
            }
            if ($isDefault) {
                $stmt = $pdo->prepare("
                    UPDATE user_addresses
                    SET is_default = 0
                    WHERE user_id = ?
                ");

                $stmt->execute([$userId]);
            }

            $stmt = $pdo->prepare("
                INSERT INTO user_addresses (
                    user_id,
                    title,
                    recipient_name,
                    recipient_phone,
                    address,
                    apartment,
                    entrance,
                    floor,
                    city,
                    postcode,
                    comment,
                    is_default
                )
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");

            $stmt->execute([
                $userId,
                $title ?: null,
                $recipientName,
                $recipientPhone,
                $address,
                $apartment ?: null,
                $entrance ?: null,
                $floor ?: null,
                $city,
                $postcode ?: null,
                $comment ?: null,
                $isDefault
            ]);


        } catch (\Throwable $th) {
            echo $th;
        }
    }






    $sql = "
        SELECT *
        FROM user_addresses
        WHERE user_id = ?
          AND is_default = 1
        LIMIT 1
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$userId]);
    $address = $stmt->fetch(PDO::FETCH_ASSOC);


    if (!$address) {
       if (!$address) {
            $pdo->rollBack();
            $_SESSION['message'] =  $action;

            header('Location: cart.php?error=no_address');
            exit;
            }
    }
    $addressId = $address['id'];


    $sql = "
        INSERT INTO orders (
            user_id,
            status,
            total,
            currency,
            customer_email,
            customer_phone,
            created_at
        )
        VALUES (
            ?,
            'pending',
            ?,
            'RUB',
            ?,
            ?,
            NOW()
        )
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        $userId,
        $finalPrice,
        $cartItems[0]->user_email,
        $address['recipient_phone']

    ]);
    $orderId = (int) $pdo->lastInsertId();

    $sqlItem = "
        INSERT INTO order_items (
            order_id,
            product_id,
            variant_id,
            product_name,
            color,
            size,
            quantity,
            price,
            discount,
            total,
            created_at
        )
        VALUES (
            ?,
            ?,
            ?,
            ?,
            ?,
            ?,
            ?,
            ?,
            ?,
            ?,
            NOW()
        )
    ";

    $stmtItem = $pdo->prepare($sqlItem);

    $stmt = $pdo->prepare("
        INSERT INTO shipments (
            order_id,
            address_id,
            status,
            delivery_method
        )
        VALUES (?, ?, ?, ?)
    ");

    $stmt->execute([
        $orderId,
        $addressId,
        'pending',
        'courier'

    ]);

    foreach ($cartItems as $item) {
        $price = (float) $item->price;
        $discount = (float) $item->discount;
        $quantity = (int) $item->quantity;
        $unitPrice =
            $price * (1 - $discount / 100);

        $itemTotal =
            $unitPrice * $quantity;


        $stmtItem->execute([
            $orderId,
            $item->product_id,
            $item->variant_id,
            $item->name,
            $item->color,
            $item->size,
            $quantity,
            $unitPrice,
            $discount,
            $itemTotal
        ]);
    }

    $client = new Client();
    $client->setAuth(
        $_ENV['CLIENT_ID'],
        $_ENV['CLIENT_TOKEN']
    );
    $idempotenceKey = bin2hex(
        random_bytes(16)
    );


    $payment = $client->createPayment(
        [
            'amount' => [
                'value' => number_format(
                    $finalPrice,
                    2,
                    '.',
                    ''
                ),
                'currency' => 'RUB',
            ],

            'confirmation' => [
                'type' => 'redirect',

                'return_url' =>
                    'https://shopco.freedev.app/cart.php',
            ],

            'capture' => true,

            'description' =>
                'Заказ №' . $orderId,

            'metadata' => [
                'order_id' => (string) $orderId,
            ],
        ],

        $idempotenceKey
    );
    $sql = "
        INSERT INTO payments (
            order_id,
            provider,
            provider_payment_id,
            status,
            amount,
            currency,
            created_at
        )
        VALUES (
            ?,
            'yookassa',
            ?,
            ?,
            ?,
            'RUB',
            NOW()
        )
    ";

    $stmt = $pdo->prepare($sql);

    $stmt->execute([
        $orderId,
        $payment->getId(),
        $payment->getStatus(),
        $finalPrice
    ]);


    $pdo->commit();



    $link =
        $payment
            ->getConfirmation()
            ->getConfirmationUrl();

    header('Location: ' . $link);
    exit;


} catch (Throwable $e) {
    echo $e;
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    error_log($e->getMessage());
    http_response_code(500);
    exit('Ошибка создания заказа');
}