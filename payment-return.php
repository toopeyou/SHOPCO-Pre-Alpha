<?php
require_once __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/lib/db.php';

session_start();
$userId = (int) $_SESSION['user_id'];

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

use YooKassa\Client;
$client = new Client();
$client->setAuth(
    $_ENV['CLIENT_ID'],
    $_ENV['CLIENT_TOKEN']
);

$orderId = $_GET['order_id'] ?? null;

if (!$orderId) {die('Заказ не найден');}

$stmt = $pdo->prepare( "SELECT provider_payment_id FROM payments WHERE order_id = ?");
$stmt->execute([$orderId]);
$order = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$order) { die('Заказ не найден');}

$payment = $client->getPaymentInfo($order['provider_payment_id']);

if ($payment->getStatus() === 'succeeded') {
    $stmt = $pdo->prepare( "UPDATE orders SET status = 'paid' WHERE id = ?");
    $stmt->execute([$orderId]);
    $stmt = $pdo->prepare( "UPDATE payments SET status = 'paid' WHERE order_id = ?");
    $stmt->execute([$orderId]);   
    $stmt = $pdo->prepare( "UPDATE shipments SET status = 'shipped' WHERE order_id = ?");
    $stmt->execute([$orderId]);
    $stmt = $pdo->prepare(
        "SELECT variant_id, quantity FROM order_items WHERE order_id = ?"
    );
    $stmt->execute([$orderId]);

    $variants = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($variants as $variant) {
        $stmt = $pdo->prepare( "UPDATE product_variants SET stock = stock - ? WHERE id = ?");

        $stmt->execute([$variant['quantity'],$variant['variant_id']]);
    }
  
    $stmt = $pdo->prepare("DELETE FROM cart_items WHERE user_id = ?");
    $stmt->execute([$userId]);
    header('Location: cart.php');


    exit;

} else {
    echo 'Платёж ещё не подтверждён. Статус: '. $payment->getStatus();
}