<?php

session_start();

require_once('../../db.php');

if (!isset($_SESSION['user_id'])) {
    header('Location: /login.php');
    exit;
}
$userId = (int) $_SESSION['user_id'];


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
$payment = trim($_POST['payment'] ?? '');

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

if (!$payment) {
    header('Location: ../../../../userpage.php');
    exit;
}else {
    header('Location: ../../../../payment.php');
    exit;
}

