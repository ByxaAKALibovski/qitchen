<?php
class CreateBasketTables {
    
    /**
     * Создание таблиц корзины
     * 
     * @param Database $db Экземпляр класса работы с базой данных
     */
    public function up($db) {
        // Создание таблицы категорий (для связи с блюдами)
        $query = "CREATE TABLE IF NOT EXISTS " . $db->escapeIdentifier('category') . " (
            id_category INT AUTO_INCREMENT PRIMARY KEY,
            title VARCHAR(255) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=" . DB_CHARSET . " COLLATE=" . DB_CHARSET . "_general_ci";
        
        $db->query($query);
        
        // Создание таблицы блюд (для связи с корзиной)
        $query = "CREATE TABLE IF NOT EXISTS " . $db->escapeIdentifier('dish') . " (
            id_dish INT AUTO_INCREMENT PRIMARY KEY,
            id_category INT NOT NULL,
            title VARCHAR(255) NOT NULL,
            compound TEXT,
            description TEXT,
            weight VARCHAR(50),
            price DECIMAL(10, 2) NOT NULL,
            calories VARCHAR(50),
            image_link VARCHAR(255),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (id_category) REFERENCES " . $db->escapeIdentifier('category') . " (id_category) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=" . DB_CHARSET . " COLLATE=" . DB_CHARSET . "_general_ci";
        
        $db->query($query);
        
        // Создание таблицы корзины
        $query = "CREATE TABLE IF NOT EXISTS " . $db->escapeIdentifier('basket') . " (
            id_basket INT AUTO_INCREMENT PRIMARY KEY,
            id_users INT NOT NULL,
            active TINYINT(1) DEFAULT 1 COMMENT '1-active, 0-inactive',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (id_users) REFERENCES " . $db->escapeIdentifier('users') . " (id_users) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=" . DB_CHARSET . " COLLATE=" . DB_CHARSET . "_general_ci";
        
        $db->query($query);
        
        // Создание таблицы элементов корзины
        $query = "CREATE TABLE IF NOT EXISTS " . $db->escapeIdentifier('basket_list') . " (
            id_basket_list INT AUTO_INCREMENT PRIMARY KEY,
            id_basket INT NOT NULL,
            id_dish INT NOT NULL,
            count INT NOT NULL DEFAULT 1,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (id_basket) REFERENCES " . $db->escapeIdentifier('basket') . " (id_basket) ON DELETE CASCADE,
            FOREIGN KEY (id_dish) REFERENCES " . $db->escapeIdentifier('dish') . " (id_dish) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=" . DB_CHARSET . " COLLATE=" . DB_CHARSET . "_general_ci";
        
        $db->query($query);
    }
} 