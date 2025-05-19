<?php
// BaseController.php уже подключен в index.php

class UsersController extends BaseController {
    
    /**
     * Вход пользователя
     */
    public function login() {
        $data = getRequestBody();
        
        // Проверка наличия обязательных полей
        if (!$this->validateRequiredFields($data, ['email', 'password'])) {
            return;
        }
        
        $email = $this->db->escape($data['email']);
        
        // Поиск пользователя по email
        $query = "SELECT * FROM " . $this->db->escapeIdentifier('users') . " WHERE email = '$email'";
        $result = $this->db->query($query);
        
        if ($result->num_rows === 0) {
            $this->sendError('Пользователь с таким email не найден', 404);
            return;
        }
        
        $user = $result->fetch_assoc();
        
        // Проверка пароля
        if (!password_verify($data['password'], $user['password'])) {
            $this->sendError('Неверный пароль', 401);
            return;
        }
        
        // Создание JWT токена
        $payload = [
            'id' => $user['id_users'],
            'email' => $user['email'],
            'op' => $user['op']
        ];
        
        $token = JWT::generate($payload);
        
        // Подготовка данных профиля
        $profileData = [
            'id' => $user['id_users'],
            'email' => $user['email'],
            'op' => $user['op']
        ];
        
        // Отправка ответа
        $this->sendSuccess([
            'token' => $token,
            'profile_data' => $profileData
        ], 'Вход выполнен успешно');
    }
    
    /**
     * Регистрация нового пользователя
     */
    public function register() {
        $data = getRequestBody();
        
        // Проверка наличия обязательных полей
        if (!$this->validateRequiredFields($data, ['email', 'password'])) {
            return;
        }
        
        $email = $this->db->escape($data['email']);
        
        // Проверка, существует ли уже пользователь с таким email
        $query = "SELECT * FROM " . $this->db->escapeIdentifier('users') . " WHERE email = '$email'";
        $result = $this->db->query($query);
        
        if ($result->num_rows > 0) {
            $this->sendError('Пользователь с таким email уже существует', 409);
            return;
        }
        
        // Хеширование пароля
        $passwordHash = password_hash($data['password'], PASSWORD_DEFAULT);
        
        // Добавление нового пользователя
        $query = "INSERT INTO " . $this->db->escapeIdentifier('users') . " (email, password) VALUES ('$email', '$passwordHash')";
        $this->db->query($query);
        
        $userId = $this->db->getLastInsertId();
        
        // Создание JWT токена
        $payload = [
            'id' => $userId,
            'email' => $email,
            'op' => 2 // обычный пользователь
        ];
        
        $token = JWT::generate($payload);
        
        // Подготовка данных профиля
        $profileData = [
            'id' => $userId,
            'email' => $email,
            'op' => 2
        ];
        
        // Создание активной корзины для пользователя
        $query = "INSERT INTO " . $this->db->escapeIdentifier('basket') . " (id_users, active) VALUES ($userId, 1)";
        $this->db->query($query);
        
        // Отправка ответа
        $this->sendSuccess([
            'token' => $token,
            'profile_data' => $profileData
        ], 'Регистрация выполнена успешно', 201);
    }
    
    /**
     * Получение данных профиля
     */
    public function profile() {
        // Проверка авторизации
        $user = authenticate();
        
        if (!$user) {
            $this->sendError('Требуется авторизация', 401);
            return;
        }
        
        // Получение информации о пользователе
        $userId = $user['id'];
        $query = "SELECT id_users, email, op FROM " . $this->db->escapeIdentifier('users') . " WHERE id_users = $userId";
        $result = $this->db->query($query);
        
        if ($result->num_rows === 0) {
            $this->sendError('Пользователь не найден', 404);
            return;
        }
        
        $userData = $result->fetch_assoc();
        
        // Получение адресов пользователя
        $query = "SELECT * FROM " . $this->db->escapeIdentifier('address') . " WHERE id_users = $userId";
        $addressResult = $this->db->query($query);
        
        $addresses = [];
        while ($address = $addressResult->fetch_assoc()) {
            $addresses[] = $address;
        }
        
        // Объединение данных
        $profileData = [
            'user' => $userData,
            'addresses' => $addresses
        ];
        
        // Отправка ответа
        $this->sendSuccess($profileData);
    }
    
    /**
     * Обновление пароля пользователя
     */
    public function updatePassword() {
        // Проверка авторизации
        $user = authenticate();
        
        if (!$user) {
            $this->sendError('Требуется авторизация', 401);
            return;
        }
        
        $data = getRequestBody();
        
        // Проверка наличия обязательных полей
        if (!$this->validateRequiredFields($data, ['password', 'new_password'])) {
            return;
        }
        
        $userId = $user['id'];
        
        // Получение текущего пароля
        $query = "SELECT password FROM " . $this->db->escapeIdentifier('users') . " WHERE id_users = $userId";
        $result = $this->db->query($query);
        
        if ($result->num_rows === 0) {
            $this->sendError('Пользователь не найден', 404);
            return;
        }
        
        $userData = $result->fetch_assoc();
        
        // Проверка текущего пароля
        if (!password_verify($data['password'], $userData['password'])) {
            $this->sendError('Текущий пароль указан неверно', 401);
            return;
        }
        
        // Хеширование нового пароля
        $newPasswordHash = password_hash($data['new_password'], PASSWORD_DEFAULT);
        
        // Обновление пароля
        $query = "UPDATE " . $this->db->escapeIdentifier('users') . " SET password = '$newPasswordHash' WHERE id_users = $userId";
        $this->db->query($query);
        
        // Отправка ответа
        $this->sendSuccess(null, 'Пароль успешно обновлен');
    }
} 