<?php

class Request {
    private $params;
    private $body;
    private $headers;
    private $method;
    
    public function __construct() {
        $this->method = $_SERVER['REQUEST_METHOD'];
        $this->headers = $this->getRequestHeaders();
        $this->body = $this->getRequestBody();
        $this->params = $_GET;
    }
    
    /**
     * Возвращает метод запроса (GET, POST, PUT, DELETE)
     * 
     * @return string
     */
    public function getMethod() {
        return $this->method;
    }
    
    /**
     * Возвращает все параметры запроса или конкретный по имени
     * 
     * @param string|null $name Имя параметра
     * @param mixed $default Значение по умолчанию
     * @return mixed
     */
    public function getParam($name = null, $default = null) {
        if ($name === null) {
            return $this->params;
        }
        return isset($this->params[$name]) ? $this->params[$name] : $default;
    }
    
    /**
     * Возвращает тело запроса как массив или конкретное поле
     * 
     * @param string|null $field Имя поля
     * @param mixed $default Значение по умолчанию
     * @return mixed
     */
    public function getBody($field = null, $default = null) {
        if ($field === null) {
            return $this->body;
        }
        return isset($this->body[$field]) ? $this->body[$field] : $default;
    }
    
    /**
     * Возвращает заголовок запроса по имени
     * 
     * @param string|null $name Имя заголовка
     * @return string|array|null
     */
    public function getHeader($name = null) {
        if ($name === null) {
            return $this->headers;
        }
        
        $name = strtolower($name);
        return isset($this->headers[$name]) ? $this->headers[$name] : null;
    }
    
    /**
     * Возвращает заголовок авторизации без префикса "Bearer"
     * 
     * @return string|null
     */
    public function getAuthToken() {
        $auth = $this->getHeader('Authorization');
        
        if ($auth && strpos($auth, 'Bearer ') === 0) {
            return substr($auth, 7);
        }
        
        return null;
    }
    
    /**
     * Получение всех заголовков запроса
     * 
     * @return array
     */
    private function getRequestHeaders() {
        $headers = [];
        
        // Если используется PHP при Apache
        if (function_exists('apache_request_headers')) {
            $requestHeaders = apache_request_headers();
            $requestHeaders = array_change_key_case($requestHeaders, CASE_LOWER);
            return $requestHeaders;
        }
        
        // Заголовки из переменной сервера
        foreach ($_SERVER as $key => $value) {
            if (strpos($key, 'HTTP_') === 0) {
                $header = str_replace(' ', '-', ucwords(str_replace('_', ' ', strtolower(substr($key, 5)))));
                $headers[strtolower($header)] = $value;
            }
        }
        
        return $headers;
    }
    
    /**
     * Получение тела запроса
     * 
     * @return array
     */
    private function getRequestBody() {
        $body = [];
        
        // Получаем содержимое тела запроса
        $content = file_get_contents('php://input');
        
        if (!empty($content)) {
            $data = json_decode($content, true);
            
            if (json_last_error() === JSON_ERROR_NONE) {
                $body = $data;
            }
        }
        
        // Для POST запросов добавляем данные из $_POST
        if ($this->method === 'POST' && !empty($_POST)) {
            $body = array_merge($body, $_POST);
        }
        
        // Добавляем загруженные файлы
        if (!empty($_FILES)) {
            $body['files'] = $_FILES;
        }
        
        return $body;
    }
} 