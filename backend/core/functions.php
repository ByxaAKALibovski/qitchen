<?php
/**
 * Получение данных из тела запроса
 * 
 * @return array Данные из тела запроса
 */
function getRequestBody() {
    $content = file_get_contents('php://input');
    
    // Пробуем декодировать JSON
    $data = json_decode($content, true);
    
    // Если не удалось, возвращаем пустой массив или $_POST
    if (json_last_error() !== JSON_ERROR_NONE) {
        return !empty($_POST) ? $_POST : [];
    }
    
    return $data ?: [];
}

/**
 * Получение данных из GET-запроса
 *
 * @param string $key Ключ параметра
 * @param mixed $default Значение по умолчанию
 * @return mixed Значение параметра
 */
function getQueryParam($key, $default = null) {
    return isset($_GET[$key]) ? $_GET[$key] : $default;
}

/**
 * Получение заголовка запроса
 *
 * @param string $name Название заголовка
 * @return string|null Значение заголовка
 */
function getHeader($name) {
    $headerName = 'HTTP_' . strtoupper(str_replace('-', '_', $name));
    
    return isset($_SERVER[$headerName]) ? $_SERVER[$headerName] : null;
}

/**
 * Получение загруженного файла
 *
 * @param string $name Имя поля формы
 * @return array|null Данные файла
 */
function getUploadedFile($name) {
    return isset($_FILES[$name]) ? $_FILES[$name] : null;
}

/**
 * Получение токена из заголовка Authorization
 * 
 * @return string|null Токен или null, если он не найден
 */
function getAuthToken() {
    return getBearerToken();
}

/**
 * Проверка авторизации пользователя
 * 
 * @param bool $adminRequired Требуется ли права администратора
 * @return array|false Данные пользователя или false при ошибке
 */
function authenticate($adminRequired = false) {
    if ($adminRequired) {
        return AuthMiddleware::authenticateAdmin();
    }
    return AuthMiddleware::authenticate();
}

/**
 * Проверка авторизации администратора
 * 
 * @return array|false Данные администратора или false при ошибке
 */
function authenticateAdmin() {
    return authenticate(true);
}

/**
 * Загрузка изображения
 * 
 * @param string $file Имя файла в $_FILES
 * @param string $targetDir Директория для сохранения
 * @return string|false Путь к загруженному файлу или false при ошибке
 */
function uploadImage($file, $targetDir = 'images') {
    // Проверка, существует ли файл
    if (!isset($_FILES[$file]) || $_FILES[$file]['error'] !== UPLOAD_ERR_OK) {
        return false;
    }
    
    $tmpName = $_FILES[$file]['tmp_name'];
    $fileName = $_FILES[$file]['name'];
    
    // Проверка типа файла
    $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
    $fileType = mime_content_type($tmpName);
    
    if (!in_array($fileType, $allowedTypes)) {
        return false;
    }
    
    // Генерация уникального имени файла
    $extension = pathinfo($fileName, PATHINFO_EXTENSION);
    $newFileName = uniqid() . '.' . $extension;
    
    // Путь для сохранения
    $fullDir = UPLOAD_DIR . '/' . $targetDir;
    
    // Создание директории, если она не существует
    if (!file_exists($fullDir)) {
        mkdir($fullDir, 0777, true);
    }
    
    $targetFile = $fullDir . '/' . $newFileName;
    
    // Перемещение файла
    if (move_uploaded_file($tmpName, $targetFile)) {
        // Возвращаем относительный путь к файлу
        return 'uploads/' . $targetDir . '/' . $newFileName;
    }
    
    return false;
}

/**
 * Получение авторизационного токена
 *
 * @return string|null Токен авторизации
 */
function getBearerToken() {
    $headers = null;
    
    if (isset($_SERVER['Authorization'])) {
        $headers = trim($_SERVER['Authorization']);
    } else if (isset($_SERVER['HTTP_AUTHORIZATION'])) {
        $headers = trim($_SERVER['HTTP_AUTHORIZATION']);
    } else if (function_exists('apache_request_headers')) {
        $requestHeaders = apache_request_headers();
        if (is_array($requestHeaders)) {
            $requestHeaders = array_change_key_case($requestHeaders, CASE_LOWER);
            if (isset($requestHeaders['authorization'])) {
                $headers = trim($requestHeaders['authorization']);
            }
        }
    }
    
    if ($headers) {
        if (preg_match('/Bearer\s(\S+)/', $headers, $matches)) {
            return $matches[1];
        }
    }
    
    return null;
} 