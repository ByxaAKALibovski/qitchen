<?php
require_once BASE_PATH . '/controllers/BaseController.php';

class CategoryController extends BaseController {
    
    /**
     * Получение одной категории по ID
     * 
     * @param array $params Параметры маршрута
     */
    public function getOne($params) {
        if (!isset($params['id']) || empty($params['id'])) {
            $this->sendError('Идентификатор категории не указан', 400);
            return;
        }
        
        $categoryId = (int)$params['id'];
        
        // Получение данных категории
        $query = "SELECT * FROM " . $this->db->escapeIdentifier('category') . " 
                  WHERE id_category = $categoryId";
        $result = $this->db->query($query);
        
        if ($result->num_rows === 0) {
            $this->sendError('Категория не найдена', 404);
            return;
        }
        
        $category = $result->fetch_assoc();
        
        // Получение блюд в этой категории
        $query = "SELECT * FROM " . $this->db->escapeIdentifier('dish') . " 
                  WHERE id_category = $categoryId";
        $dishesResult = $this->db->query($query);
        
        $dishes = [];
        while ($dish = $dishesResult->fetch_assoc()) {
            $dishes[] = $dish;
        }
        
        // Объединение данных
        $categoryData = [
            'category' => $category,
            'dishes' => $dishes
        ];
        
        // Отправка ответа
        $this->sendSuccess($categoryData);
    }
    
    /**
     * Получение всех категорий
     */
    public function getAll() {
        // Получение всех категорий
        $query = "SELECT c.*, COUNT(d.id_dish) as dishes_count 
                  FROM " . $this->db->escapeIdentifier('category') . " c
                  LEFT JOIN " . $this->db->escapeIdentifier('dish') . " d ON c.id_category = d.id_category
                  GROUP BY c.id_category
                  ORDER BY c.title ASC";
        $result = $this->db->query($query);
        
        $categories = [];
        while ($category = $result->fetch_assoc()) {
            $categories[] = $category;
        }
        
        // Отправка ответа
        $this->sendSuccess($categories);
    }
    
    /**
     * Обновление категории (только для администраторов)
     * 
     * @param array $params Параметры маршрута
     */
    public function update($params) {
        // Проверка авторизации как администратор
        $user = authenticate(true);
        $data = getRequestBody();
        
        if (!isset($params['id']) || empty($params['id'])) {
            $this->sendError('Идентификатор категории не указан', 400);
            return;
        }
        
        // Проверка наличия обязательных полей
        if (!$this->validateRequiredFields($data, ['title'])) {
            return;
        }
        
        $categoryId = (int)$params['id'];
        $title = $this->db->escape($data['title']);
        
        // Проверка существования категории
        $query = "SELECT * FROM " . $this->db->escapeIdentifier('category') . " 
                  WHERE id_category = $categoryId";
        $result = $this->db->query($query);
        
        if ($result->num_rows === 0) {
            $this->sendError('Категория не найдена', 404);
            return;
        }
        
        // Обновление категории
        $query = "UPDATE " . $this->db->escapeIdentifier('category') . " 
                  SET title = '$title' WHERE id_category = $categoryId";
        $this->db->query($query);
        
        // Получение обновленной категории
        $query = "SELECT * FROM " . $this->db->escapeIdentifier('category') . " 
                  WHERE id_category = $categoryId";
        $result = $this->db->query($query);
        $categoryData = $result->fetch_assoc();
        
        // Отправка ответа
        $this->sendSuccess($categoryData, 'Категория успешно обновлена');
    }
    
    /**
     * Удаление категории (только для администраторов)
     * 
     * @param array $params Параметры маршрута
     */
    public function delete($params) {
        // Проверка авторизации как администратор
        $user = authenticate(true);
        
        if (!isset($params['id']) || empty($params['id'])) {
            $this->sendError('Идентификатор категории не указан', 400);
            return;
        }
        
        $categoryId = (int)$params['id'];
        
        // Проверка существования категории
        $query = "SELECT * FROM " . $this->db->escapeIdentifier('category') . " 
                  WHERE id_category = $categoryId";
        $result = $this->db->query($query);
        
        if ($result->num_rows === 0) {
            $this->sendError('Категория не найдена', 404);
            return;
        }
        
        // Проверка наличия блюд в категории
        $query = "SELECT COUNT(*) as count FROM " . $this->db->escapeIdentifier('dish') . " 
                  WHERE id_category = $categoryId";
        $result = $this->db->query($query);
        $count = $result->fetch_assoc()['count'];
        
        if ($count > 0) {
            $this->sendError('Нельзя удалить категорию, содержащую блюда', 400);
            return;
        }
        
        // Удаление категории
        $query = "DELETE FROM " . $this->db->escapeIdentifier('category') . " 
                  WHERE id_category = $categoryId";
        $this->db->query($query);
        
        // Отправка ответа
        $this->sendSuccess(null, 'Категория успешно удалена');
    }
    
    /**
     * Создание новой категории (только для администраторов)
     */
    public function create() {
        // Проверка авторизации как администратор
        $user = authenticate(true);
        $data = getRequestBody();
        
        // Проверка наличия обязательных полей
        if (!$this->validateRequiredFields($data, ['title'])) {
            return;
        }
        
        $title = $this->db->escape($data['title']);
        
        // Проверка уникальности названия
        $query = "SELECT * FROM " . $this->db->escapeIdentifier('category') . " 
                  WHERE title = '$title'";
        $result = $this->db->query($query);
        
        if ($result->num_rows > 0) {
            $this->sendError('Категория с таким названием уже существует', 409);
            return;
        }
        
        // Создание новой категории
        $query = "INSERT INTO " . $this->db->escapeIdentifier('category') . " (title) 
                  VALUES ('$title')";
        $this->db->query($query);
        
        $categoryId = $this->db->getLastInsertId();
        
        // Получение созданной категории
        $query = "SELECT * FROM " . $this->db->escapeIdentifier('category') . " 
                  WHERE id_category = $categoryId";
        $result = $this->db->query($query);
        $categoryData = $result->fetch_assoc();
        
        // Отправка ответа
        $this->sendSuccess($categoryData, 'Категория успешно создана', 201);
    }
} 