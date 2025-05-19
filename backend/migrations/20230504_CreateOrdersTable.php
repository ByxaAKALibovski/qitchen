<?php
class CreateOrdersTable {
    
    /**
     * Создание таблицы заказов
     * 
     * @param Database $db Экземпляр класса работы с базой данных
     */
    public function up($db) {
        $query = "CREATE TABLE IF NOT EXISTS " . $db->escapeIdentifier('orders') . " (
            id_orders INT AUTO_INCREMENT PRIMARY KEY,
            id_users INT NOT NULL,
            id_basket INT NOT NULL,
            id_address INT NOT NULL,
            name VARCHAR(255) NOT NULL,
            email VARCHAR(255) NOT NULL,
            phone VARCHAR(20) NOT NULL,
            total_price DECIMAL(10, 2) NOT NULL,
            status TINYINT(1) NOT NULL DEFAULT 1 COMMENT '1-В процессе, 2-Доставлено, 3-Отменено',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (id_users) REFERENCES " . $db->escapeIdentifier('users') . " (id_users) ON DELETE CASCADE,
            FOREIGN KEY (id_basket) REFERENCES " . $db->escapeIdentifier('basket') . " (id_basket) ON DELETE CASCADE,
            FOREIGN KEY (id_address) REFERENCES " . $db->escapeIdentifier('address') . " (id_address) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=" . DB_CHARSET . " COLLATE=" . DB_CHARSET . "_general_ci";
        
        $db->query($query);
    }
} 