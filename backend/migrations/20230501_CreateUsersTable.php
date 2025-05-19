<?php
class CreateUsersTable {
    
    /**
     * Создание таблицы пользователей
     * 
     * @param Database $db Экземпляр класса работы с базой данных
     */
    public function up($db) {
        $query = "CREATE TABLE IF NOT EXISTS " . $db->escapeIdentifier('users') . " (
            id_users INT AUTO_INCREMENT PRIMARY KEY,
            email VARCHAR(255) NOT NULL UNIQUE,
            password VARCHAR(255) NOT NULL,
            op TINYINT(1) NOT NULL DEFAULT 2 COMMENT '1-admin 2-default user',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=" . DB_CHARSET . " COLLATE=" . DB_CHARSET . "_general_ci";
        
        $result = $db->query($query);
        
        // Создание пользователя-администратора по умолчанию (email: admin@qitchen.ru, password: admin123)
        $query = "INSERT INTO " . $db->escapeIdentifier('users') . " (email, password, op) 
                 VALUES ('admin@qitchen.ru', '" . password_hash('admin123', PASSWORD_DEFAULT) . "', 1)
                 ON DUPLICATE KEY UPDATE email = email";
        
        $db->query($query);
    }
} 