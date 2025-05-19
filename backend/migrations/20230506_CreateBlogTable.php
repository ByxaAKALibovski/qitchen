<?php
class CreateBlogTable {
    
    /**
     * Создание таблицы блога
     * 
     * @param Database $db Экземпляр класса работы с базой данных
     */
    public function up($db) {
        $query = "CREATE TABLE IF NOT EXISTS " . $db->escapeIdentifier('blog') . " (
            id_blog INT AUTO_INCREMENT PRIMARY KEY,
            title VARCHAR(255) NOT NULL,
            text_prev TEXT NOT NULL,
            description TEXT NOT NULL,
            image_link VARCHAR(255),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=" . DB_CHARSET . " COLLATE=" . DB_CHARSET . "_general_ci";
        
        $db->query($query);
    }
} 