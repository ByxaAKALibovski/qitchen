<?php

class AuthMiddleware {
    private static $currentUser = null;
    
    /**
     * Проверка авторизации пользователя
     * 
     * @param Request $request Запрос пользователя
     * @return bool True, если пользователь авторизован
     */
    public static function isAuthenticated(Request $request) {
        // Получение токена
        $token = $request->getBearerToken();
        
        if (!$token) {
            return false;
        }
        
        // Проверка токена
        $payload = JWT::decode($token);
        
        if (!$payload || !isset($payload['id_users'])) {
            return false;
        }
        
        // Загрузка пользователя
        $conn = getDbConnection();
        $stmt = $conn->prepare("SELECT * FROM users WHERE id_users = :id");
        $stmt->execute(['id' => $payload['id_users']]);
        
        $user = $stmt->fetch();
        
        if (!$user) {
            return false;
        }
        
        self::$currentUser = $user;
        return true;
    }
    
    /**
     * Проверка, является ли пользователь администратором
     * 
     * @param Request $request Запрос пользователя
     * @return bool True, если пользователь администратор
     */
    public static function isAdmin(Request $request) {
        if (!self::isAuthenticated($request)) {
            return false;
        }
        
        return self::$currentUser['op'] == 1;
    }
    
    /**
     * Получение текущего авторизованного пользователя
     * 
     * @return array|null Данные пользователя или null, если пользователь не авторизован
     */
    public static function getCurrentUser() {
        return self::$currentUser;
    }
    
    /**
     * Middleware для проверки авторизации пользователя
     * 
     * @param Request $request Запрос пользователя
     * @return bool True, если пользователь авторизован
     */
    public static function auth(Request $request) {
        if (!self::isAuthenticated($request)) {
            Response::unauthorized();
            return false;
        }
        
        return true;
    }
    
    /**
     * Middleware для проверки прав администратора
     * 
     * @param Request $request Запрос пользователя
     * @return bool True, если пользователь имеет права администратора
     */
    public static function admin(Request $request) {
        if (!self::isAdmin($request)) {
            Response::forbidden('Требуются права администратора');
            return false;
        }
        
        return true;
    }
} 