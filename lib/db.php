<?php

require_once __DIR__ . '/../vendor/autoload.php';


$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

$pdo = new PDO(
    'mysql:host=' . $_ENV['DB_HOST'] .
    ';dbname=' . $_ENV['DB_DATABASE'] .
    ';port=' . $_ENV['DB_PORT'] .
    ';charset=' . $_ENV['DB_CHARSET'],
    $_ENV['DB_USERNAME'],
    $_ENV['DB_PASSWORD'],
    [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]
);