<?php

class Migration {
    private $conn;
    private $migrationTable = 'migrations';
    
    public function __construct() {
        try {
            // Подключение к MySQL без указания базы данных
            $this->conn = new PDO(
                'mysql:host=' . DB_HOST . ';charset=utf8',
                DB_USER,
                DB_PASS,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false
                ]
            );
        } catch (PDOException $e) {
            die(json_encode(['error' => 'Ошибка подключения к базе данных: ' . $e->getMessage()]));
        }
    }
    
    /**
     * Создание базы данных, если она не существует
     */
    public function createDatabase() {
        try {
            $this->conn->exec("CREATE DATABASE IF NOT EXISTS " . DB_NAME . " CHARACTER SET utf8 COLLATE utf8_general_ci");
            $this->conn->exec("USE " . DB_NAME);
            
            echo "База данных успешно создана или уже существует\n";
            return true;
        } catch (PDOException $e) {
            die(json_encode(['error' => 'Ошибка при создании базы данных: ' . $e->getMessage()]));
        }
    }
    
    /**
     * Создание таблицы миграций
     */
    public function createMigrationsTable() {
        try {
            $this->conn->exec("USE " . DB_NAME);
            
            $sql = "CREATE TABLE IF NOT EXISTS " . $this->migrationTable . " (
                id INT AUTO_INCREMENT PRIMARY KEY,
                migration VARCHAR(255) NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )";
            
            $this->conn->exec($sql);
            
            echo "Таблица миграций успешно создана или уже существует\n";
            return true;
        } catch (PDOException $e) {
            die(json_encode(['error' => 'Ошибка при создании таблицы миграций: ' . $e->getMessage()]));
        }
    }
    
    /**
     * Получить список выполненных миграций
     *
     * @return array Список выполненных миграций
     */
    public function getAppliedMigrations() {
        $this->conn->exec("USE " . DB_NAME);
        
        $stmt = $this->conn->prepare("SELECT migration FROM " . $this->migrationTable);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
    
    /**
     * Сохранить миграцию в таблицу миграций
     *
     * @param string $migration Имя миграции
     */
    public function saveMigration($migration) {
        $this->conn->exec("USE " . DB_NAME);
        
        $stmt = $this->conn->prepare("INSERT INTO " . $this->migrationTable . " (migration) VALUES (:migration)");
        $stmt->execute(['migration' => $migration]);
    }
    
    /**
     * Получение имени класса из имени файла миграции, исключая числовой префикс
     *
     * @param string $filename Имя файла миграции
     * @return string Имя класса
     */
    private function getClassNameFromFileName($filename) {
        // Подгружаем файл и анализируем его содержимое для поиска классов
        $filePath = MIGRATION_DIR . '/' . $filename;
        if (!file_exists($filePath)) {
            return false;
        }
        
        $content = file_get_contents($filePath);
        
        // Используем регулярное выражение для поиска имени класса
        if (preg_match('/class\s+([a-zA-Z0-9_]+)/', $content, $matches)) {
            return $matches[1];
        }
        
        // Если не удалось найти класс, вернем имя на основе имени файла
        $filename = pathinfo($filename, PATHINFO_FILENAME);
        $parts = explode('_', $filename);
        
        // Удаляем первую часть, если она является числовой (например, 001)
        if (isset($parts[0]) && is_numeric($parts[0])) {
            array_shift($parts);
        }
        
        // Преобразуем каждую часть, делая первую букву заглавной
        foreach ($parts as &$part) {
            $part = ucfirst($part);
        }
        
        // Соединяем части вместе
        return implode('', $parts);
    }
    
    /**
     * Создание всех необходимых таблиц и выполнение миграций
     */
    public function migrate() {
        $this->createDatabase();
        $this->createMigrationsTable();
        
        $appliedMigrations = $this->getAppliedMigrations();
        
        // Вывод отладочной информации
        echo "Путь к директории миграций: " . MIGRATION_DIR . "\n";
        
        // Получение списка файлов миграций
        $files = scandir(MIGRATION_DIR);
        echo "Найдены файлы в директории миграций: " . implode(", ", $files) . "\n";
        
        $migrations = array_diff($files, ['.', '..', 'Migration.php']);
        echo "Файлы миграций для применения: " . implode(", ", $migrations) . "\n";
        
        // Отфильтровать файлы, которые еще не были применены
        $newMigrations = array_filter($migrations, function($migration) use ($appliedMigrations) {
            return !in_array($migration, $appliedMigrations);
        });
        
        echo "Новые миграции для применения: " . implode(", ", $newMigrations) . "\n";
        
        // Применить новые миграции
        foreach ($newMigrations as $migration) {
            $migrationPath = MIGRATION_DIR . '/' . $migration;
            echo "Загрузка миграции из файла: {$migrationPath}\n";
            
            if (!file_exists($migrationPath)) {
                echo "ОШИБКА: Файл миграции не найден: {$migrationPath}\n";
                continue;
            }
            
            require_once $migrationPath;
            
            // Получаем имя класса миграции на основе имени файла
            $className = $this->getClassNameFromFileName($migration);
            echo "Имя класса миграции: {$className}\n";
            
            if (class_exists($className)) {
                $instance = new $className();
                
                echo "Применение миграции {$migration}\n";
                
                $instance->up($this->conn);
                
                $this->saveMigration($migration);
                
                echo "Миграция {$migration} успешно применена\n";
            } else {
                echo "ОШИБКА: Класс {$className} не найден в файле миграции\n";
            }
        }
        
        if (empty($newMigrations)) {
            echo "Все миграции уже применены\n";
        }
    }
} 