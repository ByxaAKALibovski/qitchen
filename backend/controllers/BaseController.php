<?php
class BaseController {
    protected $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    /**
     * Отправка ответа в формате JSON
     * 
     * @param mixed $data Данные для отправки
     * @param int $statusCode HTTP-код ответа
     */
    protected function sendResponse($data, $statusCode = 200) {
        // Установка заголовков
        header('Content-Type: application/json');
        http_response_code($statusCode);
        
        // Отправка данных
        echo json_encode($data);
        exit();
    }
    
    /**
     * Отправка успешного ответа
     * 
     * @param mixed $data Данные для отправки
     * @param string $message Сообщение об успехе
     * @param int $statusCode HTTP-код ответа
     */
    protected function sendSuccess($data = null, $message = 'Операция выполнена успешно', $statusCode = 200) {
        $response = [
            'status' => 'success',
            'message' => $message
        ];
        
        if ($data !== null) {
            $response['data'] = $data;
        }
        
        $this->sendResponse($response, $statusCode);
    }
    
    /**
     * Отправка ошибки
     * 
     * @param string $message Сообщение об ошибке
     * @param int $statusCode HTTP-код ответа
     */
    protected function sendError($message = 'Ошибка выполнения операции', $statusCode = 400) {
        $response = [
            'status' => 'error',
            'message' => $message
        ];
        
        $this->sendResponse($response, $statusCode);
    }
    
    /**
     * Проверка наличия обязательных полей в данных запроса
     * 
     * @param array $data Данные запроса
     * @param array $required Массив обязательных полей
     * @return bool Результат проверки
     */
    protected function validateRequiredFields($data, $required) {
        foreach ($required as $field) {
            if (!isset($data[$field]) || empty($data[$field])) {
                $this->sendError("Поле '$field' обязательно для заполнения", 400);
                return false;
            }
        }
        
        return true;
    }
} 