<?php

class FileUpload {
    private $file;
    private $directory;
    private $maxSize;
    private $allowedExtensions;
    private $error = null;
    
    /**
     * Конструктор класса
     *
     * @param array $file Данные загруженного файла
     * @param string $directory Директория для сохранения файла
     * @param int $maxSize Максимальный размер файла в байтах
     * @param array $allowedExtensions Разрешенные расширения файлов
     */
    public function __construct($file, $directory = UPLOAD_DIR, $maxSize = MAX_FILE_SIZE, $allowedExtensions = ALLOWED_EXTENSIONS) {
        $this->file = $file;
        $this->directory = rtrim($directory, '/') . '/';
        $this->maxSize = $maxSize;
        $this->allowedExtensions = $allowedExtensions;
        
        // Проверка существования директории
        if (!is_dir($this->directory)) {
            mkdir($this->directory, 0777, true);
        }
    }
    
    /**
     * Загрузка файла с уникальным именем
     *
     * @return string|bool Имя сохраненного файла или false при ошибке
     */
    public function upload() {
        // Проверка наличия файла
        if (!isset($this->file) || $this->file['error'] !== 0) {
            $this->error = 'Файл не загружен или произошла ошибка при загрузке';
            return false;
        }
        
        // Проверка размера файла
        if ($this->file['size'] > $this->maxSize) {
            $this->error = 'Размер файла превышает допустимый';
            return false;
        }
        
        // Проверка расширения файла
        $extension = strtolower(pathinfo($this->file['name'], PATHINFO_EXTENSION));
        if (!in_array($extension, $this->allowedExtensions)) {
            $this->error = 'Недопустимое расширение файла';
            return false;
        }
        
        // Генерация уникального имени файла
        $newFileName = uniqid() . '.' . $extension;
        $destination = $this->directory . $newFileName;
        
        // Перемещение загруженного файла
        if (!move_uploaded_file($this->file['tmp_name'], $destination)) {
            $this->error = 'Не удалось сохранить файл';
            return false;
        }
        
        return $newFileName;
    }
    
    /**
     * Загрузка изображения с изменением размера
     *
     * @param int $maxWidth Максимальная ширина изображения
     * @param int $maxHeight Максимальная высота изображения
     * @return string|bool Имя сохраненного изображения или false при ошибке
     */
    public function uploadImage($maxWidth = 1200, $maxHeight = 1200) {
        // Проверка наличия файла
        if (!isset($this->file) || $this->file['error'] !== 0) {
            $this->error = 'Файл не загружен или произошла ошибка при загрузке';
            return false;
        }
        
        // Проверка, что файл является изображением
        $imageInfo = getimagesize($this->file['tmp_name']);
        if ($imageInfo === false) {
            $this->error = 'Загружаемый файл не является изображением';
            return false;
        }
        
        // Проверка размера файла
        if ($this->file['size'] > $this->maxSize) {
            $this->error = 'Размер файла превышает допустимый';
            return false;
        }
        
        // Проверка расширения файла
        $extension = strtolower(pathinfo($this->file['name'], PATHINFO_EXTENSION));
        if (!in_array($extension, $this->allowedExtensions)) {
            $this->error = 'Недопустимое расширение файла';
            return false;
        }
        
        // Генерация уникального имени файла
        $newFileName = uniqid() . '.' . $extension;
        $destination = $this->directory . $newFileName;
        
        // Перемещение загруженного файла
        if (!move_uploaded_file($this->file['tmp_name'], $destination)) {
            $this->error = 'Не удалось сохранить файл';
            return false;
        }
        
        return $newFileName;
    }
    
    /**
     * Получение сообщения об ошибке
     *
     * @return string|null Сообщение об ошибке
     */
    public function getError() {
        return $this->error;
    }
} 