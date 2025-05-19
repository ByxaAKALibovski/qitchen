<?php

// Конфигурация базы данных
define('DB_HOST', 'localhost');
define('DB_NAME', 'qitchen_db');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// Конфигурация JWT
define('JWT_SECRET', 'qitchen_secret_key_for_jwt_tokens');
define('JWT_EXPIRE', 86400); // 24 часа в секундах

// Пути к директориям
define('BASE_PATH', __DIR__ . '/..');
define('UPLOAD_DIR', BASE_PATH . '/uploads/');
define('IMAGES_DIR', UPLOAD_DIR . '/images/');

// Конфигурация загрузки изображений
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_EXTENSIONS', ['jpg', 'jpeg', 'png', 'gif', 'webp']);

// Функция для подключения к базе данных
function getDbConnection() {
    static $conn = null;
    
    if ($conn === null) {
        try {
            $conn = new PDO(
                "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET,
                DB_USER,
                DB_PASS,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false
                ]
            );
        } catch (PDOException $e) {
            die(json_encode(['error' => 'Ошибка подключения к базе данных: ' . $e->getMessage()]));
        }
    }
    
    return $conn;
} 