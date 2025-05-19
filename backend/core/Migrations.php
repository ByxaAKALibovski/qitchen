<?php
class Migrations {
    private $db;
    private $conn;
    private $migrationsTable = 'migrations';
    
    public function __construct() {
        $this->db = Database::getInstance();
        $this->conn = $this->db->getConnection();
    }
    
    /**
     * Инициализация таблицы миграций
     */
    public function init() {
        // Создание базы данных, если она не существует
        $this->conn->query("CREATE DATABASE IF NOT EXISTS " . $this->db->escapeIdentifier(DB_NAME) . " CHARACTER SET " . DB_CHARSET . " COLLATE " . DB_CHARSET . "_general_ci");
        $this->conn->select_db(DB_NAME);
        
        // Создание таблицы миграций, если она не существует
        $query = "CREATE TABLE IF NOT EXISTS " . $this->db->escapeIdentifier($this->migrationsTable) . " (
            id INT AUTO_INCREMENT PRIMARY KEY,
            migration VARCHAR(255) NOT NULL,
            executed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=" . DB_CHARSET . " COLLATE=" . DB_CHARSET . "_general_ci";
        
        $this->conn->query($query);
    }
    
    /**
     * Получение списка выполненных миграций
     * 
     * @return array Список выполненных миграций
     */
    public function getExecuted() {
        $executed = [];
        
        $query = "SELECT migration FROM " . $this->db->escapeIdentifier($this->migrationsTable);
        $result = $this->conn->query($query);
        
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $executed[] = $row['migration'];
            }
        }
        
        return $executed;
    }
    
    /**
     * Получение списка файлов миграций
     * 
     * @return array Список файлов миграций
     */
    public function getMigrationFiles() {
        $migrationsDir = BASE_PATH . '/migrations';
        
        if (!is_dir($migrationsDir)) {
            mkdir($migrationsDir, 0777, true);
        }
        
        $files = scandir($migrationsDir);
        $migrations = [];
        
        foreach ($files as $file) {
            if ($file === '.' || $file === '..') {
                continue;
            }
            
            if (pathinfo($file, PATHINFO_EXTENSION) === 'php') {
                $migrations[] = $file;
            }
        }
        
        sort($migrations);
        return $migrations;
    }
    
    /**
     * Выполнение миграций
     * 
     * @return array Результат выполнения миграций
     */
    public function run() {
        $this->init();
        
        $executed = $this->getExecuted();
        $files = $this->getMigrationFiles();
        
        $results = [];
        
        foreach ($files as $file) {
            if (in_array($file, $executed)) {
                continue;
            }
            
            $migrationFile = BASE_PATH . '/migrations/' . $file;
            
            if (file_exists($migrationFile)) {
                require_once $migrationFile;
                
                // Удаляем префикс даты (например, "20230501_") из имени файла
                $className = preg_replace('/^\d{8}_/', '', pathinfo($file, PATHINFO_FILENAME));
                
                if (class_exists($className)) {
                    try {
                        $migration = new $className();
                        
                        if (method_exists($migration, 'up')) {
                            $migration->up($this->db);
                            
                            // Добавление миграции в список выполненных
                            $query = "INSERT INTO " . $this->db->escapeIdentifier($this->migrationsTable) . " (migration) VALUES ('" . $this->db->escape($file) . "')";
                            $this->conn->query($query);
                            
                            $results[$file] = 'Успешно выполнена';
                        } else {
                            $results[$file] = 'Ошибка: метод up не найден';
                        }
                    } catch (Exception $e) {
                        $results[$file] = 'Ошибка: ' . $e->getMessage();
                    }
                } else {
                    $results[$file] = 'Ошибка: класс не найден';
                }
            } else {
                $results[$file] = 'Ошибка: файл миграции не найден';
            }
        }
        
        return $results;
    }
} 