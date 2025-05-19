<?php
class JWT {
    /**
     * Генерация JWT токена
     * 
     * @param array $payload Данные для включения в токен
     * @return string JWT токен
     */
    public static function generate($payload) {
        // Создание заголовка
        $header = [
            'alg' => 'HS256',
            'typ' => 'JWT'
        ];
        
        // Создание полезной нагрузки
        $payload['iat'] = time(); // время создания
        $payload['exp'] = time() + JWT_EXPIRE; // время истечения
        
        // Кодирование заголовка и полезной нагрузки
        $base64UrlHeader = self::base64UrlEncode(json_encode($header));
        $base64UrlPayload = self::base64UrlEncode(json_encode($payload));
        
        // Создание подписи
        $signature = hash_hmac('sha256', $base64UrlHeader . "." . $base64UrlPayload, JWT_SECRET, true);
        $base64UrlSignature = self::base64UrlEncode($signature);
        
        // Создание JWT токена
        $jwt = $base64UrlHeader . "." . $base64UrlPayload . "." . $base64UrlSignature;
        
        return $jwt;
    }
    
    /**
     * Проверка и декодирование JWT токена
     * 
     * @param string $jwt JWT токен
     * @return array|false Данные из токена или false в случае ошибки
     */
    public static function decode($jwt) {
        // Разделение токена на части
        $tokenParts = explode('.', $jwt);
        if (count($tokenParts) != 3) {
            return false;
        }
        
        $header = $tokenParts[0];
        $payload = $tokenParts[1];
        $signatureProvided = $tokenParts[2];
        
        // Повторное вычисление подписи
        $signature = hash_hmac('sha256', $header . "." . $payload, JWT_SECRET, true);
        $base64UrlSignature = self::base64UrlEncode($signature);
        
        // Проверка подписи
        if ($base64UrlSignature !== $signatureProvided) {
            return false;
        }
        
        // Декодирование полезной нагрузки
        $decodedPayload = json_decode(self::base64UrlDecode($payload), true);
        
        // Проверка срока действия
        if (isset($decodedPayload['exp']) && time() > $decodedPayload['exp']) {
            return false;
        }
        
        return $decodedPayload;
    }
    
    /**
     * Кодирование в base64url
     */
    private static function base64UrlEncode($data) {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }
    
    /**
     * Декодирование из base64url
     */
    private static function base64UrlDecode($data) {
        return base64_decode(strtr($data, '-_', '+/') . str_repeat('=', 3 - (3 + strlen($data)) % 4));
    }
} 