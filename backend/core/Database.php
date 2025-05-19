<?php
class Database {
    private $connection;
    private static $instance = null;
    
    // Приватный конструктор для паттерна Singleton
    private function __construct() {
        try {
            $this->connection = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
            
            if ($this->connection->connect_error) {
                throw new Exception("Ошибка подключения к базе данных: " . $this->connection->connect_error);
            }
            
            // Установка кодировки
            $this->connection->set_charset(DB_CHARSET);
        } catch (Exception $e) {
            echo json_encode([
                'status' => 'error',
                'message' => $e->getMessage()
            ]);
            exit();
        }
    }
    
    // Метод для получения экземпляра класса (Singleton)
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new Database();
        }
        return self::$instance;
    }
    
    // Получение объекта подключения
    public function getConnection() {
        return $this->connection;
    }
    
    // Экранирование значений для предотвращения SQL-инъекций
    public function escape($value) {
        return $this->connection->real_escape_string($value);
    }
    
    // Экранирование идентификаторов (имен таблиц, столбцов)
    public function escapeIdentifier($identifier) {
        // Разделение на части по точке (для конструкций вида database.table)
        $parts = explode('.', $identifier);
        
        // Экранирование каждой части
        foreach ($parts as &$part) {
            // Удаление существующих обратных кавычек
            $part = str_replace('`', '', $part);
            // Заключение в обратные кавычки
            $part = '`' . $part . '`';
        }
        
        // Объединение обратно с точкой
        return implode('.', $parts);
    }
    
    // Выполнение SQL-запроса
    public function query($sql) {
        $result = $this->connection->query($sql);
        
        if ($this->connection->error) {
            throw new Exception("Ошибка SQL-запроса: " . $this->connection->error);
        }
        
        return $result;
    }
    
    // Подготовленные выражения для безопасных запросов
    public function prepare($sql) {
        return $this->connection->prepare($sql);
    }
    
    // Получение ID последней вставленной записи
    public function getLastInsertId() {
        return $this->connection->insert_id;
    }
    
    // Закрытие подключения
    public function close() {
        if ($this->connection) {
            $this->connection->close();
        }
    }
} 