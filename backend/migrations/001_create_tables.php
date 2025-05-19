<?php

class CreateTables {
    public function up($conn) {
        // 1. Таблица users
        $conn->exec("CREATE TABLE IF NOT EXISTS users (
            id_users INT AUTO_INCREMENT PRIMARY KEY,
            FIO VARCHAR(255) NOT NULL,
            phone VARCHAR(20) NOT NULL,
            email VARCHAR(255) NOT NULL UNIQUE,
            password VARCHAR(255) NOT NULL,
            op TINYINT DEFAULT 0
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8");
        
        // 2. Таблица category
        $conn->exec("CREATE TABLE IF NOT EXISTS category (
            id_category INT AUTO_INCREMENT PRIMARY KEY,
            category_name VARCHAR(255) NOT NULL,
            description TEXT NOT NULL,
            photo VARCHAR(255) NOT NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8");
        
        // 3. Таблица experts
        $conn->exec("CREATE TABLE IF NOT EXISTS experts (
            id_expert INT AUTO_INCREMENT PRIMARY KEY,
            FIO VARCHAR(255) NOT NULL,
            category_name VARCHAR(255) NOT NULL,
            photo VARCHAR(255) NOT NULL,
            expert_discription TEXT NOT NULL,
            expert_education TEXT NOT NULL,
            expert_experience TEXT NOT NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8");
        
        // 4. Таблица reviews
        $conn->exec("CREATE TABLE IF NOT EXISTS reviews (
            id_reviews INT AUTO_INCREMENT PRIMARY KEY,
            phone VARCHAR(20) NOT NULL,
            text_positive TEXT,
            text_negative TEXT,
            id_expert INT NOT NULL,
            FOREIGN KEY (id_expert) REFERENCES experts(id_expert) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8");
        
        // 5. Таблица services
        $conn->exec("CREATE TABLE IF NOT EXISTS services (
            id_services INT AUTO_INCREMENT PRIMARY KEY,
            category_name VARCHAR(255) NOT NULL,
            services_name VARCHAR(255) NOT NULL,
            price DECIMAL(10, 2) NOT NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8");
        
        // 6. Таблица application
        $conn->exec("CREATE TABLE IF NOT EXISTS application (
            id_application INT AUTO_INCREMENT PRIMARY KEY,
            user_name VARCHAR(255) NOT NULL,
            phone VARCHAR(20) NOT NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8");
        
        // 7. Таблица records
        $conn->exec("CREATE TABLE IF NOT EXISTS records (
            id_records INT AUTO_INCREMENT PRIMARY KEY,
            fio_user VARCHAR(255) NOT NULL,
            phone_user VARCHAR(20) NOT NULL,
            expert VARCHAR(255) NOT NULL,
            date DATE NOT NULL,
            time TIME NOT NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8");
        
        // Создание администратора по умолчанию
        $hashedPassword = password_hash('admin', PASSWORD_DEFAULT);
        $stmt = $conn->prepare("INSERT INTO users (FIO, phone, email, password, op) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute(['Администратор', '+79999999999', 'admin@medcenter.com', $hashedPassword, 1]);
    }
} 