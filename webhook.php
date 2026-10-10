<?php

declare(strict_types=1);
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/lib/db.php';
use Dotenv\Dotenv;
use YooKassa\Client;
header('Content-Type: text/plain; charset=utf-8');
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Method Not Allowed');
}

try {
    $dotenv = Dotenv::createImmutable(__DIR__);
    $dotenv->safeLoad();
    $shopId = $_ENV['CLIENT_ID'] ?? '';
    $secretKey = $_ENV['CLIENT_TOKEN'] ?? '';
    if ($shopId === '' || $secretKey === '') {
        throw new RuntimeException('YooKassa credentials are missing');
    }
    $rawBody = file_get_contents('php://input');
    $data = json_decode($rawBody, true);
    if (
        !is_array($data) ||
        ($data['event'] ?? '') !== 'payment.succeeded' ||
        ($data['object']['id'] ?? '') === ''
    ) {
        http_response_code(400);
        exit('Invalid notification');
    }

    $paymentId = (string) $data['object']['id'];

    $client = new Client();
    $client->setAuth($shopId, $secretKey);

    $payment = $client->getPaymentInfo($paymentId);
    if (
        $payment->getId() !== $paymentId ||
        $payment->getStatus() !== 'succeeded'
    ) {
        http_response_code(400);
        exit('Payment not succeeded');
    }

    $stmt = $pdo->prepare(
        'SELECT order_id FROM payments
         WHERE provider_payment_id = ?
         LIMIT 1'
    );
    $stmt->execute([$paymentId]);

    $paymentRow = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$paymentRow) {
          http_response_code(500);
        exit('Local payment not found');
    }

    $orderId = $paymentRow['order_id'];

    // Важно: все таблицы должны поддерживать транзакции (InnoDB).
    $pdo->beginTransaction();
    $stmt = $pdo->prepare(
        'SELECT status FROM orders WHERE id = ? FOR UPDATE'
    );
    $stmt->execute([$orderId]);

    $order = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$order) {
        throw new RuntimeException('Order not found');
    }

    if ($order['status'] === 'paid') {
        $pdo->commit();
        http_response_code(200);
        exit('OK');
    }


    $stmt = $pdo->prepare(
        'SELECT status FROM payments
         WHERE order_id = ? AND provider_payment_id = ?
         LIMIT 1 FOR UPDATE'
    );
    $stmt->execute([$orderId, $paymentId]);

    $localPayment = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$localPayment) {
        throw new RuntimeException('Payment/order mismatch');
    }

    $stmt = $pdo->prepare(
        'SELECT variant_id, quantity
         FROM order_items
         WHERE order_id = ?'
    );
    $stmt->execute([$orderId]);

    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (!$items) {
        throw new RuntimeException('Order has no items');
    }

    $stockStmt = $pdo->prepare(
        'UPDATE product_variants
         SET stock = stock - ?
         WHERE id = ? AND stock >= ?'
    );

    foreach ($items as $item) {
        $quantity = (int) $item['quantity'];
        $variantId = (int) $item['variant_id'];

        if ($quantity <= 0 || $variantId <= 0) {
            throw new RuntimeException('Invalid order item');
        }

        $stockStmt->execute([
            $quantity,
            $variantId,
            $quantity
        ]);

        if ($stockStmt->rowCount() !== 1) {
            throw new RuntimeException(
                'Insufficient stock or variant not found'
            );
        }
    }

    // Подтверждаем заказ и платёж
    $stmt = $pdo->prepare(
        "UPDATE orders SET status = 'paid' WHERE id = ?"
    );
    $stmt->execute([$orderId]);

    $stmt = $pdo->prepare(
        "UPDATE payments SET status = 'paid'
         WHERE order_id = ? AND provider_payment_id = ?"
    );
    $stmt->execute([$orderId, $paymentId]);


    $stmt = $pdo->prepare(
        "UPDATE shipments SET status = 'delivered' WHERE order_id = ?"
    );
    $stmt->execute([$orderId]);

    $pdo->commit();

    http_response_code(200);
    echo 'OK';

} catch (Throwable $e) {
    if (isset($pdo) && $pdo instanceof PDO && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
error_log(
    date('c') . ' ' .
    $e->getMessage() . PHP_EOL .
    $e->getTraceAsString()
);
  
    error_log('YooKassa webhook error: ' . $e->getMessage());

    http_response_code(500);
    echo 'Processing error';
}