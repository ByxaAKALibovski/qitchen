<?php
require_once './utils/JWT.php';
require_once './utils/Response.php';

class AuthMiddleware {
    private static $currentUser = null;
    
    /**
     * Аутентификация пользователя по токену
     *
     * @return array|null Данные пользователя или null при ошибке
     */
    public static function authenticate() {
        // Получение токена из заголовка
        $token = self::getBearerToken();
        
        if (!$token) {
            Response::unauthorized('Токен авторизации отсутствует');
            return null;
        }
        
        // Проверка токена
        $payload = JWT::decode($token);
        
        if (!$payload) {
            Response::unauthorized('Недействительный токен авторизации');
            return null;
        }
        
        return $payload;
    }
    
    /**
     * Аутентификация администратора
     *
     * @return array|null Данные администратора или null при ошибке
     */
    public static function authenticateAdmin() {
        $user = self::authenticate();
        
        if (!$user) {
            return null;
        }
        
        // Проверка прав администратора (значение op = 1)
        if (!isset($user['op']) || $user['op'] != 1) {
            Response::forbidden('Недостаточно прав для выполнения операции');
            return null;
        }
        
        return $user;
    }
    
    /**
     * Получение токена из заголовка Authorization
     *
     * @return string|null Токен авторизации или null
     */
    private static function getBearerToken() {
        $headers = null;
        
        // Получение заголовков
        if (isset($_SERVER['Authorization'])) {
            $headers = trim($_SERVER['Authorization']);
        } else if (isset($_SERVER['HTTP_AUTHORIZATION'])) {
            $headers = trim($_SERVER['HTTP_AUTHORIZATION']);
        } else if (function_exists('apache_request_headers')) {
            $requestHeaders = apache_request_headers();
            
            // Преобразование имен заголовков к единому регистру
            if (is_array($requestHeaders)) {
                $requestHeaders = array_change_key_case($requestHeaders, CASE_LOWER);
                if (isset($requestHeaders['authorization'])) {
                    $headers = trim($requestHeaders['authorization']);
                }
            }
        }
        
        // Извлечение токена
        if ($headers) {
            if (preg_match('/Bearer\s(\S+)/', $headers, $matches)) {
                return $matches[1];
            }
        }
        
        return null;
    }
    
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