<?php
// Включение отображения ошибок для отладки
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<html><head><title>API Debug</title>";
echo "<style>
    body { font-family: Arial, sans-serif; margin: 20px; line-height: 1.6; }
    h1, h2, h3 { color: #333; }
    .success { color: green; }
    .error { color: red; }
    .warning { color: orange; }
    pre { background: #f5f5f5; padding: 10px; border-radius: 5px; overflow: auto; }
    .section { margin-bottom: 30px; border-bottom: 1px solid #eee; padding-bottom: 20px; }
    table { border-collapse: collapse; width: 100%; }
    table, th, td { border: 1px solid #ddd; }
    th, td { padding: 8px; text-align: left; }
    th { background-color: #f2f2f2; }
</style>";
echo "</head><body>";
echo "<h1>API Диагностика</h1>";

// Функция для проверки доступности файла/директории
function checkPath($path, $name) {
    if (file_exists($path)) {
        if (is_dir($path)) {
            echo "<p class='success'>✓ Директория $name существует: $path</p>";
            return true;
        } else {
            echo "<p class='success'>✓ Файл $name существует: $path</p>";
            return true;
        }
    } else {
        echo "<p class='error'>✗ Путь $name не существует: $path</p>";
        return false;
    }
}

// Информация о сервере
echo "<div class='section'>";
echo "<h2>Информация о сервере</h2>";
echo "<table>";
echo "<tr><th>Параметр</th><th>Значение</th></tr>";
echo "<tr><td>PHP Version</td><td>" . phpversion() . "</td></tr>";
echo "<tr><td>Server Software</td><td>" . $_SERVER['SERVER_SOFTWARE'] . "</td></tr>";
echo "<tr><td>HTTP_HOST</td><td>" . $_SERVER['HTTP_HOST'] . "</td></tr>";
echo "<tr><td>DOCUMENT_ROOT</td><td>" . $_SERVER['DOCUMENT_ROOT'] . "</td></tr>";
echo "<tr><td>SCRIPT_FILENAME</td><td>" . $_SERVER['SCRIPT_FILENAME'] . "</td></tr>";
echo "<tr><td>SCRIPT_NAME</td><td>" . $_SERVER['SCRIPT_NAME'] . "</td></tr>";
echo "<tr><td>REQUEST_URI</td><td>" . $_SERVER['REQUEST_URI'] . "</td></tr>";
echo "<tr><td>PHP_SELF</td><td>" . $_SERVER['PHP_SELF'] . "</td></tr>";
echo "<tr><td>dirname(SCRIPT_NAME)</td><td>" . dirname($_SERVER['SCRIPT_NAME']) . "</td></tr>";
echo "</table>";
echo "</div>";

// Проверка структуры API
echo "<div class='section'>";
echo "<h2>Проверка структуры API</h2>";

// Базовые пути
$baseDir = __DIR__;
$configDir = $baseDir . '/config';
$controllersDir = $baseDir . '/controllers';
$utilsDir = $baseDir . '/utils';
$middlewareDir = $baseDir . '/middleware';
$uploadsDir = $baseDir . '/uploads';

// Проверка основных директорий
checkPath($baseDir, 'API Root');
checkPath($configDir, 'Config');
checkPath($controllersDir, 'Controllers');
checkPath($utilsDir, 'Utils');
checkPath($middlewareDir, 'Middleware');
checkPath($uploadsDir, 'Uploads');

// Проверка основных файлов
$indexFile = $baseDir . '/index.php';
$configFile = $configDir . '/config.php';
$htaccessFile = $baseDir . '/.htaccess';

checkPath($indexFile, 'index.php');
checkPath($configFile, 'config.php');
checkPath($htaccessFile, '.htaccess');

echo "</div>";

// Проверка .htaccess
echo "<div class='section'>";
echo "<h2>Содержимое .htaccess</h2>";
if (file_exists($htaccessFile)) {
    echo "<pre>" . htmlspecialchars(file_get_contents($htaccessFile)) . "</pre>";
} else {
    echo "<p class='error'>Файл .htaccess не найден!</p>";
}
echo "</div>";

// Проверка маршрутизации
echo "<div class='section'>";
echo "<h2>Тест маршрутизации</h2>";

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
echo "<p>URI после parse_url: <code>$uri</code></p>";

$uriParts = explode('/', $uri);
echo "<p>URI части: </p><pre>" . print_r($uriParts, true) . "</pre>";

// Симуляция маршрутизации
echo "<p>Симуляция маршрутизации для текущего URI:</p>";
$apiPrefix = 'api';
$controllerName = !empty($uriParts[2]) ? ucfirst($uriParts[2]) : '';
$method = !empty($uriParts[3]) ? $uriParts[3] : '';
$param = !empty($uriParts[4]) ? $uriParts[4] : null;

echo "<table>";
echo "<tr><th>Параметр</th><th>Значение</th></tr>";
echo "<tr><td>API Prefix</td><td>$apiPrefix</td></tr>";
echo "<tr><td>Controller Name</td><td>$controllerName</td></tr>";
echo "<tr><td>Method</td><td>$method</td></tr>";
echo "<tr><td>Parameter</td><td>$param</td></tr>";
echo "</table>";

// Проверка наличия контроллера
if (!empty($controllerName)) {
    $controllerFile = $controllersDir . '/' . $controllerName . 'Controller.php';
    if (file_exists($controllerFile)) {
        echo "<p class='success'>✓ Контроллер найден: $controllerFile</p>";
    } else {
        echo "<p class='error'>✗ Контроллер не найден: $controllerFile</p>";
    }
}
echo "</div>";

// Проверка конфигурации
echo "<div class='section'>";
echo "<h2>Проверка конфигурации</h2>";

if (file_exists($configFile)) {
    include_once $configFile;
    
    echo "<h3>Константы конфигурации:</h3>";
    echo "<table>";
    echo "<tr><th>Константа</th><th>Значение</th></tr>";
    
    $constants = [
        'DB_HOST', 'DB_NAME', 'DB_USER', 'DB_CHARSET',
        'JWT_SECRET', 'JWT_EXPIRE',
        'BASE_PATH', 'UPLOAD_DIR', 'IMAGES_DIR',
        'MAX_FILE_SIZE'
    ];
    
    foreach ($constants as $constant) {
        if (defined($constant)) {
            $value = constant($constant);
            if (is_array($value)) {
                $value = implode(', ', $value);
            }
            echo "<tr><td>$constant</td><td>" . htmlspecialchars($value) . "</td></tr>";
        } else {
            echo "<tr><td>$constant</td><td class='error'>Не определена</td></tr>";
        }
    }
    
    echo "</table>";
    
    // Проверка соединения с БД
    echo "<h3>Проверка соединения с базой данных:</h3>";
    
    if (function_exists('getDbConnection')) {
        try {
            $conn = getDbConnection();
            echo "<p class='success'>✓ Соединение с базой данных успешно установлено</p>";
        } catch (Exception $e) {
            echo "<p class='error'>✗ Ошибка соединения с базой данных: " . $e->getMessage() . "</p>";
        }
    } else {
        echo "<p class='error'>✗ Функция getDbConnection не определена</p>";
    }
} else {
    echo "<p class='error'>✗ Файл конфигурации не найден!</p>";
}
echo "</div>";

// Проверка контроллеров
echo "<div class='section'>";
echo "<h2>Доступные контроллеры</h2>";

if (is_dir($controllersDir)) {
    $controllers = scandir($controllersDir);
    echo "<ul>";
    foreach ($controllers as $controller) {
        if ($controller != "." && $controller != ".." && pathinfo($controller, PATHINFO_EXTENSION) == 'php') {
            echo "<li>$controller</li>";
        }
    }
    echo "</ul>";
} else {
    echo "<p class='error'>✗ Директория контроллеров не найдена!</p>";
}
echo "</div>";

// Рекомендации по устранению проблем
echo "<div class='section'>";
echo "<h2>Рекомендации по устранению проблем</h2>";
echo "<ol>";
echo "<li>Убедитесь, что файл <code>.htaccess</code> правильно настроен для перенаправления запросов на index.php</li>";
echo "<li>Проверьте, что в файле <code>index.php</code> правильно определяется маршрут API</li>";
echo "<li>Проверьте настройки виртуального хоста в OpenServer (домен должен указывать на корневую директорию API)</li>";
echo "<li>Убедитесь, что PHP имеет права на чтение и запись в директории проекта</li>";
echo "<li>Проверьте логи ошибок Apache/PHP для получения дополнительной информации</li>";
echo "</ol>";
echo "</div>";

echo "</body></html>"; 