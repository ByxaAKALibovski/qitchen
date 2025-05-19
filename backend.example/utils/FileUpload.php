<?php

class FileUpload {
    /**
     * Сохранение загруженного файла
     * 
     * @param string $fieldName Имя поля формы с файлом
     * @param string $directory Поддиректория для загрузки
     * @return string|bool Путь к загруженному файлу или false в случае ошибки
     */
    public function saveFile($fieldName, $directory = 'images') {
        if (!isset($_FILES[$fieldName]) || $_FILES[$fieldName]['error'] !== UPLOAD_ERR_OK) {
            return false;
        }
        
        $file = $_FILES[$fieldName];
        
        // Проверка существования директории и создание при необходимости
        $uploadPath = UPLOAD_DIR . $directory . '/';
        
        if (!file_exists($uploadPath)) {
            if (!mkdir($uploadPath, 0755, true)) {
                return false;
            }
        }
        
        // Проверка типа файла
        $fileInfo = pathinfo($file['name']);
        $extension = strtolower($fileInfo['extension']);
        
        if (!in_array($extension, ALLOWED_EXTENSIONS)) {
            return false;
        }
        
        // Проверка размера файла
        if ($file['size'] > MAX_FILE_SIZE) {
            return false;
        }
        
        // Генерация уникального имени файла
        $fileName = uniqid() . '.' . $extension;
        $filePath = $uploadPath . $fileName;
        
        // Загрузка файла
        if (!move_uploaded_file($file['tmp_name'], $filePath)) {
            return false;
        }
        
        // Возвращаем относительный путь для сохранения в базе данных
        return $directory . '/' . $fileName;
    }
    
    /**
     * Загрузка файла изображения (статический метод для обратной совместимости)
     * 
     * @param array $file Массив с информацией о загружаемом файле ($_FILES['file'])
     * @param string $directory Поддиректория для загрузки
     * @return string|bool Путь к загруженному файлу или false в случае ошибки
     */
    public static function uploadImage($file, $directory = 'images') {
        // Проверка существования директории и создание при необходимости
        $uploadPath = UPLOAD_DIR . $directory . '/';
        
        if (!file_exists($uploadPath)) {
            if (!mkdir($uploadPath, 0755, true)) {
                return false;
            }
        }
        
        // Проверка типа файла
        $fileInfo = pathinfo($file['name']);
        $extension = strtolower($fileInfo['extension']);
        
        if (!in_array($extension, ALLOWED_EXTENSIONS)) {
            return false;
        }
        
        // Проверка размера файла
        if ($file['size'] > MAX_FILE_SIZE) {
            return false;
        }
        
        // Генерация уникального имени файла
        $fileName = uniqid() . '.' . $extension;
        $filePath = $uploadPath . $fileName;
        
        // Загрузка файла
        if (!move_uploaded_file($file['tmp_name'], $filePath)) {
            return false;
        }
        
        // Возвращаем относительный путь для сохранения в базе данных
        return $directory . '/' . $fileName;
    }
    
    /**
     * Удаление файла
     * 
     * @param string $filePath Путь к файлу для удаления
     * @return bool Результат операции
     */
    public static function deleteFile($filePath) {
        $fullPath = UPLOAD_DIR . $filePath;
        
        if (file_exists($fullPath)) {
            return unlink($fullPath);
        }
        
        return false;
    }
} 