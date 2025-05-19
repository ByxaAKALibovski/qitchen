<?php
// Включение отображения ошибок для отладки
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Загрузка конфигурации и необходимых классов
require_once 'config/config.php';
require_once 'core/Database.php';
require_once 'core/Migrations.php';

// Запуск миграций
$migrations = new Migrations();
$results = $migrations->run();

// Вывод результатов
echo "<h1>Результаты выполнения миграций</h1>";
echo "<pre>";
print_r($results);
echo "</pre>";

// Если запуск из командной строки
if (php_sapi_name() === 'cli') {
    echo "\nМиграции выполнены.\n";
} 