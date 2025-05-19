<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
// Настройки CORS - разрешаем запросы со всех доменов
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, Accept');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Max-Age: 86400'); // кеширование на 24 часа

// Обработка preflight запросов (OPTIONS)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    header('Content-Type: application/json');
    http_response_code(200);
    exit;
}

// Установка типа контента для обычных запросов
header('Content-Type: application/json');

// Обработка ошибок для более подробной диагностики
ini_set('display_errors', 0);
error_reporting(E_ALL);

// Функция обработки всех ошибок
function handleError($errno, $errstr, $errfile, $errline) {
    $errorResponse = [
        'status' => 'error',
        'message' => 'Внутренняя ошибка сервера',
        'details' => $errstr,
        'file' => $errfile,
        'line' => $errline
    ];
    
    // Запись ошибки в лог
    error_log("API Error: $errstr in $errfile on line $errline");
    
    // Вывод информации об ошибке
    header('Content-Type: application/json');
    http_response_code(500);
    echo json_encode($errorResponse);
    exit;
}

// Установка обработчика ошибок
set_error_handler('handleError');

require_once './config/config.php';
require_once './core/Database.php';
require_once './core/functions.php';
require_once './utils/Response.php';
require_once './utils/Request.php';
require_once './utils/JWT.php';
require_once './utils/FileUpload.php';
require_once './middleware/AuthMiddleware.php';
require_once './controllers/BaseController.php';

// Роутинг запросов
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri = explode('/', $uri);

// Определение контроллера и метода
$apiPrefix = 'api';
$controllerName = !empty($uri[2]) ? ucfirst($uri[2]) : '';
$method = !empty($uri[3]) ? $uri[3] : '';
$param = !empty($uri[4]) ? $uri[4] : null;

// Проверка, что запрос направлен к API
if (empty($controllerName) || $uri[1] !== $apiPrefix) {
    Response::json(['error' => 'Неверный запрос к API', 'uri' => $uri], 404);
    exit;
}

// Загрузка контроллера
$controllerFile = "./controllers/{$controllerName}Controller.php";
if (file_exists($controllerFile)) {
    require_once $controllerFile;
    $controllerClass = $controllerName . 'Controller';
    
    $controller = new $controllerClass();
    
    // Проверка метода
    if (method_exists($controller, $method)) {
        $request = new Request();
        $controller->$method($request, $param);
    } else {
        Response::json(['error' => 'Метод не существует'], 404);
    }
} else {
    Response::json(['error' => 'Контроллер не найден', 'controller' => $controllerName], 404);
} 