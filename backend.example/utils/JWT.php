<?php

class JWT {
    /**
     * Создание JWT токена
     * 
     * @param array $payload Данные для включения в токен
     * @return string Сгенерированный JWT токен
     */
    public static function encode($payload) {
        $header = ['alg' => 'HS256', 'typ' => 'JWT'];
        
        // Установка времени истечения, если оно не установлено
        if (!isset($payload['exp'])) {
            $payload['exp'] = time() + JWT_EXPIRE;
        }
        
        // Кодирование header и payload
        $headerEncoded = self::base64UrlEncode(json_encode($header));
        $payloadEncoded = self::base64UrlEncode(json_encode($payload));
        
        // Создание подписи
        $signature = hash_hmac('sha256', "$headerEncoded.$payloadEncoded", JWT_SECRET, true);
        $signatureEncoded = self::base64UrlEncode($signature);
        
        // Формирование токена
        return "$headerEncoded.$payloadEncoded.$signatureEncoded";
    }
    
    /**
     * Проверка JWT токена и получение полезной нагрузки
     * 
     * @param string $token JWT токен
     * @return array|bool Полезная нагрузка токена или false при неудачной проверке
     */
    public static function decode($token) {
        // Разделение токена на части
        $parts = explode('.', $token);
        
        if (count($parts) !== 3) {
            return false;
        }
        
        list($headerEncoded, $payloadEncoded, $signatureEncoded) = $parts;
        
        // Проверка подписи
        $signature = self::base64UrlDecode($signatureEncoded);
        $expectedSignature = hash_hmac('sha256', "$headerEncoded.$payloadEncoded", JWT_SECRET, true);
        
        if (!hash_equals($signature, $expectedSignature)) {
            return false;
        }
        
        // Декодирование payload
        $payload = json_decode(self::base64UrlDecode($payloadEncoded), true);
        
        // Проверка срока действия токена
        if (isset($payload['exp']) && $payload['exp'] < time()) {
            return false;
        }
        
        return $payload;
    }
    
    /**
     * Кодирование в Base64URL
     * 
     * @param string $data Данные для кодирования
     * @return string Закодированные данные
     */
    private static function base64UrlEncode($data) {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }
    
    /**
     * Декодирование из Base64URL
     * 
     * @param string $data Данные для декодирования
     * @return string Декодированные данные
     */
    private static function base64UrlDecode($data) {
        return base64_decode(strtr($data, '-_', '+/'));
    }
} 