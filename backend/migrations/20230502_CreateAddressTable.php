<?php
class CreateAddressTable {
    
    /**
     * Создание таблицы адресов
     * 
     * @param Database $db Экземпляр класса работы с базой данных
     */
    public function up($db) {
        $query = "CREATE TABLE IF NOT EXISTS " . $db->escapeIdentifier('address') . " (
            id_address INT AUTO_INCREMENT PRIMARY KEY,
            id_users INT NOT NULL,
            address TEXT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (id_users) REFERENCES " . $db->escapeIdentifier('users') . " (id_users) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=" . DB_CHARSET . " COLLATE=" . DB_CHARSET . "_general_ci";
        
        $db->query($query);
    }
} 