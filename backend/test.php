<?php
// Включение отображения ошибок для отладки
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<html><head><title>API Test</title>";
echo "<style>
    body { font-family: Arial, sans-serif; margin: 20px; line-height: 1.6; }
    .success { color: green; }
    .error { color: red; }
    pre { background: #f5f5f5; padding: 10px; border-radius: 5px; overflow: auto; }
</style>";
echo "</head><body>";
echo "<h1>API Test</h1>";

// Проверка запросов к API
echo "<h2>Тестирование запросов к API</h2>";

// Функция для выполнения тестового запроса
function makeRequest($url, $method = 'GET', $data = null, $token = null) {
    $curl = curl_init();
    
    // Базовый URL API
    $baseUrl = "http://" . $_SERVER['HTTP_HOST'] . "/api";
    $fullUrl = $baseUrl . $url;
    
    echo "<p>Запрос: <strong>$method $fullUrl</strong></p>";
    
    $options = [
        CURLOPT_URL => $fullUrl,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_CUSTOMREQUEST => $method,
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            'Accept: application/json'
        ],
    ];
    
    // Добавление авторизационного токена
    if ($token) {
        $options[CURLOPT_HTTPHEADER][] = 'Authorization: Bearer ' . $token;
    }
    
    // Добавление данных для POST/PUT запросов
    if ($data && ($method === 'POST' || $method === 'PUT')) {
        $options[CURLOPT_POSTFIELDS] = json_encode($data);
    }
    
    curl_setopt_array($curl, $options);
    
    $response = curl_exec($curl);
    $err = curl_error($curl);
    $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    
    curl_close($curl);
    
    echo "<p>HTTP код: <strong>$httpCode</strong></p>";
    
    if ($err) {
        echo "<p class='error'>Ошибка cURL: $err</p>";
        return null;
    }
    
    echo "<p>Ответ:</p>";
    echo "<pre>" . json_encode(json_decode($response), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "</pre>";
    
    return json_decode($response, true);
}

// Базовый тест - проверка работоспособности API
echo "<h3>1. Проверка работоспособности API</h3>";
makeRequest('/');

// Проверка информации о URI
echo "<h3>2. Диагностика URI</h3>";

echo "<p>SERVER['HTTP_HOST']: " . $_SERVER['HTTP_HOST'] . "</p>";
echo "<p>SERVER['REQUEST_URI']: " . $_SERVER['REQUEST_URI'] . "</p>";
echo "<p>SERVER['SCRIPT_NAME']: " . $_SERVER['SCRIPT_NAME'] . "</p>";

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
echo "<p>URI после parse_url: $uri</p>";

$uriParts = explode('/', $uri);
echo "<p>URI части: " . implode(", ", $uriParts) . "</p>";

// Тестирование маршрутов API
echo "<h3>3. Тестирование входа</h3>";
$loginData = [
    'email' => 'admin@qitchen.ru', 
    'password' => 'admin123'
];

$loginResponse = makeRequest('/users/login', 'POST', $loginData);
$token = null;
if ($loginResponse && isset($loginResponse['data']['token'])) {
    $token = $loginResponse['data']['token'];
    echo "<p class='success'>Токен получен успешно!</p>";
}

// Тестирование защищенного маршрута
if ($token) {
    echo "<h3>4. Тестирование профиля пользователя (защищенный маршрут)</h3>";
    makeRequest('/users/profile', 'GET', null, $token);
}

echo "</body></html>"; 