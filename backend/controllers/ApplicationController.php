<?php

class ApplicationController {
    /**
     * Получение списка всех заявок
     * 
     * @param Request $request Объект запроса
     */
    public function index(Request $request) {
        try {
            $conn = getDbConnection();
            
            $sql = "SELECT * FROM application ORDER BY id_application DESC";
            $stmt = $conn->prepare($sql);
            $stmt->execute();
            
            $applications = $stmt->fetchAll();
            
            Response::success($applications, 'Список заявок получен успешно');
        } catch (PDOException $e) {
            Response::error('Ошибка при получении списка заявок: ' . $e->getMessage());
        }
    }
    
    /**
     * Получение одной заявки по ID
     * 
     * @param Request $request Объект запроса
     * @param int $id ID заявки
     */
    public function show(Request $request, $id = null) {
        if (!$id) {
            Response::error('Не указан ID заявки');
            return;
        }
        
        try {
            $conn = getDbConnection();
            
            $stmt = $conn->prepare("SELECT * FROM application WHERE id_application = :id");
            $stmt->execute(['id' => $id]);
            
            $application = $stmt->fetch();
            
            if (!$application) {
                Response::notFound('Заявка не найдена');
                return;
            }
            
            Response::success($application, 'Заявка получена успешно');
        } catch (PDOException $e) {
            Response::error('Ошибка при получении заявки: ' . $e->getMessage());
        }
    }
    
    /**
     * Добавление новой заявки
     * 
     * @param Request $request Объект запроса
     */
    public function store(Request $request) {
        $data = $request->all();
        
        // Проверка обязательных полей
        if (!isset($data['user_name']) || !isset($data['phone'])) {
            Response::error('Необходимо указать имя пользователя и телефон');
            return;
        }
        
        try {
            $conn = getDbConnection();
            
            // Добавление заявки в базу данных
            $stmt = $conn->prepare("
                INSERT INTO application (user_name, phone) 
                VALUES (:user_name, :phone)
            ");
            
            $stmt->execute([
                'user_name' => $data['user_name'],
                'phone' => $data['phone']
            ]);
            
            $applicationId = $conn->lastInsertId();
            
            Response::success(['id_application' => $applicationId], 'Заявка успешно добавлена');
        } catch (PDOException $e) {
            Response::error('Ошибка при добавлении заявки: ' . $e->getMessage());
        }
    }
} 