<?php
class Router {
    private $routes = [];
    private $notFoundCallback;
    
    /**
     * Добавление GET-маршрута
     */
    public function get($path, $callback) {
        $this->addRoute('GET', $path, $callback);
        return $this;
    }
    
    /**
     * Добавление POST-маршрута
     */
    public function post($path, $callback) {
        $this->addRoute('POST', $path, $callback);
        return $this;
    }
    
    /**
     * Добавление PUT-маршрута
     */
    public function put($path, $callback) {
        $this->addRoute('PUT', $path, $callback);
        return $this;
    }
    
    /**
     * Добавление DELETE-маршрута
     */
    public function delete($path, $callback) {
        $this->addRoute('DELETE', $path, $callback);
        return $this;
    }
    
    /**
     * Добавление маршрута в коллекцию
     */
    private function addRoute($method, $path, $callback) {
        // Преобразование пути в регулярное выражение
        $pattern = $this->pathToRegex($path);
        
        $this->routes[] = [
            'method' => $method,
            'path' => $path,
            'pattern' => $pattern,
            'callback' => $callback
        ];
    }
    
    /**
     * Обработка запроса 404 Not Found
     */
    public function notFound($callback) {
        $this->notFoundCallback = $callback;
        return $this;
    }
    
    /**
     * Преобразование пути с параметрами в регулярное выражение
     */
    private function pathToRegex($path) {
        // Экранирование специальных символов
        $pattern = preg_quote($path, '/');
        
        // Замена параметров {param} на группы захвата
        $pattern = preg_replace('/\\\{([a-zA-Z0-9_]+)\\\}/', '(?P<$1>[^\/]+)', $pattern);
        
        // Добавление начала и конца строки
        $pattern = '/^' . $pattern . '$/';
        
        return $pattern;
    }
    
    /**
     * Запуск маршрутизатора
     */
    public function run() {
        // Получение метода запроса
        $method = $_SERVER['REQUEST_METHOD'];
        
        // Получение пути запроса
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        
        // Модифицировано: Улучшенное определение базового пути и очистка URI
        $basePath = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
        
        // Обработка случаев, когда API вызывается напрямую через домен
        // Если используется виртуальный хост (домен указывает прямо на директорию API)
        if ($basePath === '/' || $basePath === '\\' || empty($basePath)) {
            // Используем URI как есть
        } else if (strpos($uri, $basePath) === 0) {
            // Иначе удаляем базовый путь из URI (для случаев типа localhost/qitchen/backend)
            $uri = substr($uri, strlen($basePath));
        }
        
        // Если URI пустой или "/", то это корневой маршрут
        if (empty($uri) || $uri === '/') {
            $uri = '/';
        }
        
        // Добавим отладочное сообщение для проверки URI
        // error_log("Processed URI: " . $uri);
        
        // Поиск подходящего маршрута
        foreach ($this->routes as $route) {
            if ($route['method'] !== $method) {
                continue;
            }
            
            if (preg_match($route['pattern'], $uri, $matches)) {
                // Фильтрация параметров (удаление числовых ключей)
                $params = array_filter($matches, function($key) {
                    return !is_numeric($key);
                }, ARRAY_FILTER_USE_KEY);
                
                // Вызов обработчика маршрута
                $callback = $route['callback'];
                
                if (is_string($callback)) {
                    // Если строка вида "ControllerName@methodName"
                    list($controller, $method) = explode('@', $callback);
                    $controllerClass = $controller . 'Controller';
                    
                    // Загрузка контроллера, если он еще не загружен
                    $controllerFile = BASE_PATH . '/controllers/' . $controllerClass . '.php';
                    if (file_exists($controllerFile)) {
                        require_once $controllerFile;
                    }
                    
                    // Создание экземпляра контроллера
                    $controllerInstance = new $controllerClass();
                    
                    // Вызов метода контроллера
                    return $controllerInstance->$method($params);
                } else {
                    // Если функция обратного вызова
                    return call_user_func($callback, $params);
                }
            }
        }
        
        // Если маршрут не найден
        if ($this->notFoundCallback) {
            return call_user_func($this->notFoundCallback);
        } else {
            // Стандартный ответ 404
            header("HTTP/1.1 404 Not Found");
            echo json_encode([
                'status' => 'error',
                'message' => 'Маршрут не найден',
                'uri' => $uri
            ]);
            exit();
        }
    }
} 