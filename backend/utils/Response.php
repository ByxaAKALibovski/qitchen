<?php

class Response {
    /**
     * Отправка JSON ответа
     * 
     * @param mixed $data Данные для ответа
     * @param int $code HTTP код ответа
     */
    public static function json($data, $code = 200) {
        http_response_code($code);
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    /**
     * Отправка успешного ответа
     * 
     * @param mixed $data Данные для ответа
     * @param string $message Сообщение об успехе
     */
    public static function success($data = null, $message = 'Операция выполнена успешно') {
        self::json([
            'status' => 'success',
            'message' => $message,
            'data' => $data
        ]);
    }
    
    /**
     * Отправка ответа об ошибке
     * 
     * @param string $message Сообщение об ошибке
     * @param int $code HTTP код ошибки
     */
    public static function error($message = 'Произошла ошибка', $code = 400) {
        self::json([
            'status' => 'error',
            'message' => $message
        ], $code);
    }
    
    /**
     * Отправка ответа с ошибкой авторизации
     * 
     * @param string $message Сообщение об ошибке
     */
    public static function unauthorized($message = 'Требуется авторизация') {
        self::error($message, 401);
    }
    
    /**
     * Отправка ответа с ошибкой доступа
     * 
     * @param string $message Сообщение об ошибке
     */
    public static function forbidden($message = 'Доступ запрещен') {
        self::error($message, 403);
    }
    
    /**
     * Отправка ответа с ошибкой 404
     * 
     * @param string $message Сообщение об ошибке
     */
    public static function notFound($message = 'Ресурс не найден') {
        self::error($message, 404);
    }
} 