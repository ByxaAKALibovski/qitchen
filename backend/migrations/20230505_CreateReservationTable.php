<?php
class CreateReservationTable {
    
    /**
     * Создание таблицы бронирования
     * 
     * @param Database $db Экземпляр класса работы с базой данных
     */
    public function up($db) {
        $query = "CREATE TABLE IF NOT EXISTS " . $db->escapeIdentifier('reservation') . " (
            id_reservation INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(255) NOT NULL,
            phone VARCHAR(20) NOT NULL,
            email VARCHAR(255) NOT NULL,
            count_guest INT NOT NULL,
            date DATE NOT NULL,
            time TIME NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=" . DB_CHARSET . " COLLATE=" . DB_CHARSET . "_general_ci";
        
        $db->query($query);
    }
} 