<?php

class ServicesController {
    /**
     * Получение списка услуг
     * 
     * @param Request $request Объект запроса
     */
    public function index(Request $request) {
        try {
            $conn = getDbConnection();
            
            // Получение всех услуг
            $stmt = $conn->query("SELECT * FROM services ORDER BY category_name, services_name");
            $services = $stmt->fetchAll();
            
            Response::success($services, 'Список услуг получен успешно');
        } catch (PDOException $e) {
            Response::error('Ошибка при получении списка услуг: ' . $e->getMessage());
        }
    }
    
    /**
     * Получение услуги по ID
     * 
     * @param Request $request Объект запроса
     * @param int $id ID услуги
     */
    public function show(Request $request, $id) {
        try {
            $conn = getDbConnection();
            
            // Получение услуги по ID
            $stmt = $conn->prepare("SELECT * FROM services WHERE id_services = :id");
            $stmt->execute(['id' => $id]);
            
            $service = $stmt->fetch();
            
            if (!$service) {
                Response::notFound('Услуга не найдена');
                return;
            }
            
            Response::success($service, 'Услуга получена успешно');
        } catch (PDOException $e) {
            Response::error('Ошибка при получении услуги: ' . $e->getMessage());
        }
    }
    
    /**
     * Получение услуг по категории
     * 
     * @param Request $request Объект запроса
     * @param string $category_name Название категории
     */
    public function getByCategory(Request $request, $category_name) {
        try {
            $conn = getDbConnection();
            
            // Проверка существования категории
            $stmt = $conn->prepare("SELECT * FROM category WHERE category_name = :category_name");
            $stmt->execute(['category_name' => $category_name]);
            
            if (!$stmt->fetch()) {
                Response::notFound('Категория не найдена');
                return;
            }
            
            // Получение услуг по категории
            $stmt = $conn->prepare("SELECT * FROM services WHERE category_name = :category_name ORDER BY services_name");
            $stmt->execute(['category_name' => $category_name]);
            
            $services = $stmt->fetchAll();
            
            Response::success($services, 'Список услуг по категории получен успешно');
        } catch (PDOException $e) {
            Response::error('Ошибка при получении услуг по категории: ' . $e->getMessage());
        }
    }
    
    /**
     * Создание новой услуги (только для администраторов)
     * 
     * @param Request $request Объект запроса
     */
    public function store(Request $request) {
        // Проверка прав администратора
        if (!AuthMiddleware::admin($request)) {
            return;
        }
        
        $data = $request->all();
        
        // Проверка обязательных полей
        if (!isset($data['category_name']) || !isset($data['services_name']) || !isset($data['price'])) {
            Response::error('Все поля обязательны для заполнения');
            return;
        }
        
        try {
            $conn = getDbConnection();
            
            // Проверка существования категории
            $stmt = $conn->prepare("SELECT * FROM category WHERE category_name = :category_name");
            $stmt->execute(['category_name' => $data['category_name']]);
            
            if (!$stmt->fetch()) {
                Response::error('Указанная категория не существует');
                return;
            }
            
            // Проверка на дубликат услуги
            $stmt = $conn->prepare("
                SELECT * FROM services 
                WHERE category_name = :category_name AND services_name = :services_name
            ");
            
            $stmt->execute([
                'category_name' => $data['category_name'],
                'services_name' => $data['services_name']
            ]);
            
            if ($stmt->fetch()) {
                Response::error('Услуга с таким названием уже существует в этой категории');
                return;
            }
            
            // Создание услуги
            $stmt = $conn->prepare("
                INSERT INTO services (category_name, services_name, price) 
                VALUES (:category_name, :services_name, :price)
            ");
            
            $stmt->execute([
                'category_name' => $data['category_name'],
                'services_name' => $data['services_name'],
                'price' => $data['price']
            ]);
            
            $id = $conn->lastInsertId();
            
            Response::success(['id' => $id], 'Услуга успешно создана');
        } catch (PDOException $e) {
            Response::error('Ошибка при создании услуги: ' . $e->getMessage());
        }
    }
    
    /**
     * Обновление услуги (только для администраторов)
     * 
     * @param Request $request Объект запроса
     * @param int $id ID услуги
     */
    public function update(Request $request, $id) {
        // Проверка прав администратора
        if (!AuthMiddleware::admin($request)) {
            return;
        }
        
        $data = $request->all();
        
        // Проверка обязательных полей
        if (!isset($data['category_name']) || !isset($data['services_name']) || !isset($data['price'])) {
            Response::error('Все поля обязательны для заполнения');
            return;
        }
        
        try {
            $conn = getDbConnection();
            
            // Проверка существования услуги
            $stmt = $conn->prepare("SELECT * FROM services WHERE id_services = :id");
            $stmt->execute(['id' => $id]);
            
            if (!$stmt->fetch()) {
                Response::notFound('Услуга не найдена');
                return;
            }
            
            // Проверка существования категории
            $stmt = $conn->prepare("SELECT * FROM category WHERE category_name = :category_name");
            $stmt->execute(['category_name' => $data['category_name']]);
            
            if (!$stmt->fetch()) {
                Response::error('Указанная категория не существует');
                return;
            }
            
            // Проверка на дубликат услуги
            $stmt = $conn->prepare("
                SELECT * FROM services 
                WHERE category_name = :category_name AND services_name = :services_name AND id_services != :id
            ");
            
            $stmt->execute([
                'category_name' => $data['category_name'],
                'services_name' => $data['services_name'],
                'id' => $id
            ]);
            
            if ($stmt->fetch()) {
                Response::error('Услуга с таким названием уже существует в этой категории');
                return;
            }
            
            // Обновление услуги
            $stmt = $conn->prepare("
                UPDATE services 
                SET category_name = :category_name, 
                    services_name = :services_name, 
                    price = :price 
                WHERE id_services = :id
            ");
            
            $stmt->execute([
                'category_name' => $data['category_name'],
                'services_name' => $data['services_name'],
                'price' => $data['price'],
                'id' => $id
            ]);
            
            Response::success(null, 'Услуга успешно обновлена');
        } catch (PDOException $e) {
            Response::error('Ошибка при обновлении услуги: ' . $e->getMessage());
        }
    }
    
    /**
     * Удаление услуги (только для администраторов)
     * 
     * @param Request $request Объект запроса
     * @param int $id ID услуги
     */
    public function delete(Request $request, $id) {
        // Проверка прав администратора
        if (!AuthMiddleware::admin($request)) {
            return;
        }
        
        try {
            $conn = getDbConnection();
            
            // Проверка существования услуги
            $stmt = $conn->prepare("SELECT * FROM services WHERE id_services = :id");
            $stmt->execute(['id' => $id]);
            
            if (!$stmt->fetch()) {
                Response::notFound('Услуга не найдена');
                return;
            }
            
            // Удаление услуги
            $stmt = $conn->prepare("DELETE FROM services WHERE id_services = :id");
            $stmt->execute(['id' => $id]);
            
            Response::success(null, 'Услуга успешно удалена');
        } catch (PDOException $e) {
            Response::error('Ошибка при удалении услуги: ' . $e->getMessage());
        }
    }
} 