<?php
/**
 * Конфигурация подключения к базе данных
 */

$dbPath = __DIR__ . '/data/sto.db';
$dsn = "sqlite:$dbPath";

try {
    $pdo = new PDO($dsn);
    $pdo->exec('PRAGMA encoding = "UTF-8"');
    $pdo->exec('PRAGMA foreign_keys = ON');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Ошибка подключения к базе данных: " . $e->getMessage());
}

