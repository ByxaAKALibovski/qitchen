<?php
// BaseController.php уже подключен в index.php

class TestController extends BaseController {
    
    /**
     * Проверка работы API - public endpoint
     */
    public function check() {
        $this->sendSuccess([
            'message' => 'API работает корректно!',
            'timestamp' => time(),
            'php_version' => phpversion()
        ]);
    }
    
    /**
     * Проверка аутентификации - protected endpoint
     */
    public function auth(Request $request) {
        // Проверка авторизации
        $user = authenticate();
        
        if (!$user) {
            $this->sendError('Требуется авторизация', 401);
            return;
        }
        
        $this->sendSuccess([
            'message' => 'Аутентификация прошла успешно',
            'user' => $user
        ]);
    }
    
    /**
     * Проверка параметров запроса
     */
    public function params(Request $request, $id = null) {
        $this->sendSuccess([
            'uri_param' => $id,
            'query_params' => $_GET,
            'request_body' => getRequestBody(),
            'method' => $_SERVER['REQUEST_METHOD']
        ]);
    }
    
    /**
     * Тест подключения к базе данных
     */
    public function db() {
        try {
            // Выполняем простой запрос для проверки
            $result = $this->db->query("SHOW TABLES");
            
            $tables = [];
            while ($row = $result->fetch_array()) {
                $tables[] = $row[0];
            }
            
            $this->sendSuccess([
                'message' => 'Подключение к базе данных работает корректно',
                'tables' => $tables
            ]);
        } catch (Exception $e) {
            $this->sendError('Ошибка подключения к базе данных: ' . $e->getMessage(), 500);
        }
    }
} 