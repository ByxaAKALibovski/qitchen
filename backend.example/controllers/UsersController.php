<?php

class UsersController {
    /**
     * Регистрация пользователя
     * 
     * @param Request $request Объект запроса
     */
    public function register(Request $request) {
        // Получение данных из запроса
        $data = $request->all();
        
        // Проверка наличия обязательных полей
        if (!isset($data['FIO']) || !isset($data['phone']) || !isset($data['email']) || !isset($data['password'])) {
            Response::error('Необходимо заполнить все обязательные поля');
            return;
        }
        
        $fio = $data['FIO'];
        $phone = $data['phone'];
        $email = $data['email'];
        $password = $data['password'];
        
        // Проверка email
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            Response::error('Неверный формат email');
            return;
        }
        
        // Проверка длины пароля
        if (strlen($password) < 6) {
            Response::error('Пароль должен содержать не менее 6 символов');
            return;
        }
        
        try {
            $conn = getDbConnection();
            
            // Проверка, не занят ли email
            $stmt = $conn->prepare("SELECT id_users FROM users WHERE email = :email");
            $stmt->execute(['email' => $email]);
            
            if ($stmt->fetch()) {
                Response::error('Email уже зарегистрирован');
                return;
            }
            
            // Хеширование пароля
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            
            // Добавление пользователя в базу данных
            $stmt = $conn->prepare("
                INSERT INTO users (FIO, phone, email, password) 
                VALUES (:fio, :phone, :email, :password)
            ");
            
            $stmt->execute([
                'fio' => $fio,
                'phone' => $phone,
                'email' => $email,
                'password' => $hashedPassword
            ]);
            
            $userId = $conn->lastInsertId();
            
            // Генерация JWT токена
            $token = JWT::encode(['id_users' => $userId]);
            
            Response::success(['token' => $token], 'Регистрация успешно завершена');
        } catch (PDOException $e) {
            Response::error('Ошибка при регистрации: ' . $e->getMessage());
        }
    }
    
    /**
     * Авторизация пользователя
     * 
     * @param Request $request Объект запроса
     */
    public function login(Request $request) {
        // Получение данных из запроса
        $data = $request->all();
        
        // Проверка наличия обязательных полей
        if (!isset($data['email']) || !isset($data['password'])) {
            Response::error('Необходимо указать email и пароль');
            return;
        }
        
        $email = $data['email'];
        $password = $data['password'];
        
        try {
            $conn = getDbConnection();
            
            // Поиск пользователя по email
            $stmt = $conn->prepare("SELECT * FROM users WHERE email = :email");
            $stmt->execute(['email' => $email]);
            
            $user = $stmt->fetch();
            
            if (!$user || !password_verify($password, $user['password'])) {
                Response::error('Неверный email или пароль');
                return;
            }
            
            // Генерация JWT токена
            $token = JWT::encode(['id_users' => $user['id_users']]);
            
            // Скрываем пароль в ответе
            unset($user['password']);
            
            Response::success([
                'token' => $token,
                'user' => $user
            ], 'Авторизация успешна');
        } catch (PDOException $e) {
            Response::error('Ошибка при авторизации: ' . $e->getMessage());
        }
    }
    
    /**
     * Выход пользователя
     * 
     * @param Request $request Объект запроса
     */
    public function logout(Request $request) {
        // Для выхода клиент должен просто удалить токен на своей стороне
        // JWT не хранит состояние на сервере, поэтому нет необходимости что-то удалять
        
        Response::success(null, 'Выход выполнен успешно');
    }
    
    /**
     * Получение профиля текущего пользователя
     * 
     * @param Request $request Объект запроса
     */
    public function profile(Request $request) {
        // Проверка авторизации
        if (!AuthMiddleware::auth($request)) {
            return;
        }
        
        $user = AuthMiddleware::getCurrentUser();
        
        // Скрываем пароль
        unset($user['password']);
        
        Response::success($user);
    }

    /**
     * Получение количества пользователей (только для администраторов)
     * 
     * @param Request $request Объект запроса
     */
    public function getCount(Request $request) {
        // Проверка авторизации
        if (!AuthMiddleware::auth($request)) {
            return;
        }
        
        $user = AuthMiddleware::getCurrentUser();
        
        // Проверка прав администратора
        if (!$user['op']) {
            Response::error('Доступ запрещен', 403);
            return;
        }
        
        try {
            $conn = getDbConnection();
            
            // Получение общего количества пользователей
            $stmt = $conn->query("SELECT COUNT(*) as count FROM users");
            $result = $stmt->fetch();
            
            $count = (int)$result['count'];
            
            Response::success(['count' => $count], 'Количество пользователей получено успешно');
        } catch (PDOException $e) {
            Response::error('Ошибка при получении количества пользователей: ' . $e->getMessage());
        }
    }
} 