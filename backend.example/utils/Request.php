<?php

class Request {
    private $body;
    private $files;
    
    public function __construct() {
        $this->files = $_FILES;
        
        // Получение данных из тела запроса
        $method = $_SERVER['REQUEST_METHOD'];
        
        if ($method === 'GET') {
            $this->body = $_GET;
        } else {
            $content = file_get_contents('php://input');
            $this->body = json_decode($content, true);
            
            // Если JSON недействителен, попробуем получить данные из POST
            if ($this->body === null && $method === 'POST') {
                $this->body = $_POST;
            }
        }
        
        // Если body все еще null, создадим пустой массив
        if ($this->body === null) {
            $this->body = [];
        }
    }
    
    /**
     * Получить все данные запроса
     * 
     * @return array Данные запроса
     */
    public function all() {
        return $this->body;
    }
    
    /**
     * Получить значение параметра
     * 
     * @param string $key Ключ параметра
     * @param mixed $default Значение по умолчанию, если параметр не найден
     * @return mixed Значение параметра или значение по умолчанию
     */
    public function get($key, $default = null) {
        return isset($this->body[$key]) ? $this->body[$key] : $default;
    }
    
    /**
     * Проверить, существует ли параметр
     * 
     * @param string $key Ключ параметра
     * @return bool True, если параметр существует
     */
    public function has($key) {
        return isset($this->body[$key]);
    }
    
    /**
     * Получить загруженный файл
     * 
     * @param string $key Ключ файла
     * @return array|null Данные файла или null, если файл не найден
     */
    public function file($key) {
        return isset($this->files[$key]) ? $this->files[$key] : null;
    }
    
    /**
     * Получить заголовок запроса
     * 
     * @param string $key Ключ заголовка
     * @return string|null Значение заголовка или null, если заголовок не найден
     */
    public function header($key) {
        $headerKey = 'HTTP_' . strtoupper(str_replace('-', '_', $key));
        return isset($_SERVER[$headerKey]) ? $_SERVER[$headerKey] : null;
    }

    /**
     * Получить авторизационный токен из заголовка
     * 
     * @return string|null Токен авторизации или null, если токен не найден
     */
    public function getBearerToken() {
        $header = getallheaders()['Authorization'];

        if ($header && preg_match('/Bearer\s+(.*)$/i', $header, $matches)) {
            return $matches[1];
        }
        
        return null;
    }
    
    /**
     * Проверить, является ли запрос AJAX запросом
     * 
     * @return bool True, если запрос является AJAX запросом
     */
    public function isAjax() {
        return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
               strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }
    
    /**
     * Получить метод запроса
     * 
     * @return string Метод запроса (GET, POST, PUT, DELETE)
     */
    public function method() {
        return $_SERVER['REQUEST_METHOD'];
    }
} 