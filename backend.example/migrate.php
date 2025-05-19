<?php
// Вывод всех ошибок для отладки
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once './config/config.php';
require_once './migrations/Migration.php';

// Запуск миграций
try {
    echo "Запуск миграции...\n";
    $migration = new Migration();
    $migration->migrate();
    echo "Миграции успешно выполнены\n";
} catch (Exception $e) {
    echo "Ошибка при выполнении миграций: " . $e->getMessage() . "\n";
    echo "Трассировка: " . $e->getTraceAsString() . "\n";
} 