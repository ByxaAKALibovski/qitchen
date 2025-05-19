<?php
require_once BASE_PATH . '/controllers/BaseController.php';

class DishController extends BaseController {
    
    /**
     * Получение одного блюда по ID
     * 
     * @param array $params Параметры маршрута
     */
    public function getOne($params) {
        if (!isset($params['id']) || empty($params['id'])) {
            $this->sendError('Идентификатор блюда не указан', 400);
            return;
        }
        
        $dishId = (int)$params['id'];
        
        // Получение данных блюда с информацией о категории
        $query = "SELECT d.*, c.title as category_title 
                  FROM " . $this->db->escapeIdentifier('dish') . " d
                  JOIN " . $this->db->escapeIdentifier('category') . " c ON d.id_category = c.id_category
                  WHERE d.id_dish = $dishId";
        $result = $this->db->query($query);
        
        if ($result->num_rows === 0) {
            $this->sendError('Блюдо не найдено', 404);
            return;
        }
        
        $dish = $result->fetch_assoc();
        
        // Отправка ответа
        $this->sendSuccess($dish);
    }
    
    /**
     * Получение всех блюд с возможностью фильтрации по категории
     */
    public function getAll() {
        $categoryId = isset($_GET['category']) ? (int)$_GET['category'] : 0;
        $search = isset($_GET['search']) ? $this->db->escape($_GET['search']) : '';
        
        // Базовый запрос
        $query = "SELECT d.*, c.title as category_title 
                  FROM " . $this->db->escapeIdentifier('dish') . " d
                  JOIN " . $this->db->escapeIdentifier('category') . " c ON d.id_category = c.id_category";
        
        // Добавление фильтров
        $whereConditions = [];
        
        if ($categoryId > 0) {
            $whereConditions[] = "d.id_category = $categoryId";
        }
        
        if (!empty($search)) {
            $whereConditions[] = "(d.title LIKE '%$search%' OR d.description LIKE '%$search%')";
        }
        
        // Формирование WHERE части запроса
        if (!empty($whereConditions)) {
            $query .= " WHERE " . implode(' AND ', $whereConditions);
        }
        
        // Добавление сортировки
        $query .= " ORDER BY d.title ASC";
        
        $result = $this->db->query($query);
        
        $dishes = [];
        while ($dish = $result->fetch_assoc()) {
            $dishes[] = $dish;
        }
        
        // Отправка ответа
        $this->sendSuccess($dishes);
    }
    
    /**
     * Удаление блюда (только для администраторов)
     * 
     * @param array $params Параметры маршрута
     */
    public function delete($params) {
        // Проверка авторизации как администратор
        $user = authenticate(true);
        
        if (!isset($params['id']) || empty($params['id'])) {
            $this->sendError('Идентификатор блюда не указан', 400);
            return;
        }
        
        $dishId = (int)$params['id'];
        
        // Проверка существования блюда
        $query = "SELECT * FROM " . $this->db->escapeIdentifier('dish') . " 
                  WHERE id_dish = $dishId";
        $result = $this->db->query($query);
        
        if ($result->num_rows === 0) {
            $this->sendError('Блюдо не найдено', 404);
            return;
        }
        
        $dish = $result->fetch_assoc();
        $imagePath = $dish['image_link'];
        
        // Удаление изображения, если оно есть
        if (!empty($imagePath) && file_exists(BASE_PATH . '/' . $imagePath)) {
            unlink(BASE_PATH . '/' . $imagePath);
        }
        
        // Удаление блюда
        $query = "DELETE FROM " . $this->db->escapeIdentifier('dish') . " 
                  WHERE id_dish = $dishId";
        $this->db->query($query);
        
        // Отправка ответа
        $this->sendSuccess(null, 'Блюдо успешно удалено');
    }
    
    /**
     * Обновление блюда (только для администраторов)
     * 
     * @param array $params Параметры маршрута
     */
    public function update($params) {
        // Проверка авторизации как администратор
        $user = authenticate(true);
        
        if (!isset($params['id']) || empty($params['id'])) {
            $this->sendError('Идентификатор блюда не указан', 400);
            return;
        }
        
        $dishId = (int)$params['id'];
        
        // Проверка существования блюда
        $query = "SELECT * FROM " . $this->db->escapeIdentifier('dish') . " 
                  WHERE id_dish = $dishId";
        $result = $this->db->query($query);
        
        if ($result->num_rows === 0) {
            $this->sendError('Блюдо не найдено', 404);
            return;
        }
        
        $dish = $result->fetch_assoc();
        $oldImagePath = $dish['image_link'];
        
        // Получение данных формы
        $title = isset($_POST['title']) ? $this->db->escape($_POST['title']) : $dish['title'];
        $categoryId = isset($_POST['id_category']) ? (int)$_POST['id_category'] : $dish['id_category'];
        $compound = isset($_POST['compound']) ? $this->db->escape($_POST['compound']) : $dish['compound'];
        $description = isset($_POST['description']) ? $this->db->escape($_POST['description']) : $dish['description'];
        $weight = isset($_POST['weight']) ? $this->db->escape($_POST['weight']) : $dish['weight'];
        $price = isset($_POST['price']) ? (float)$_POST['price'] : $dish['price'];
        $calories = isset($_POST['calories']) ? $this->db->escape($_POST['calories']) : $dish['calories'];
        
        // Обработка загруженного изображения
        $imageLink = $oldImagePath;
        
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $newImagePath = uploadImage('image', 'dishes');
            
            if ($newImagePath) {
                $imageLink = $newImagePath;
                
                // Удаление старого изображения, если оно есть
                if (!empty($oldImagePath) && file_exists(BASE_PATH . '/' . $oldImagePath)) {
                    unlink(BASE_PATH . '/' . $oldImagePath);
                }
            }
        }
        
        // Проверка существования категории
        $query = "SELECT * FROM " . $this->db->escapeIdentifier('category') . " 
                  WHERE id_category = $categoryId";
        $result = $this->db->query($query);
        
        if ($result->num_rows === 0) {
            $this->sendError('Указанная категория не существует', 404);
            return;
        }
        
        // Обновление блюда
        $query = "UPDATE " . $this->db->escapeIdentifier('dish') . " SET 
                  title = '$title',
                  id_category = $categoryId,
                  compound = '$compound',
                  description = '$description',
                  weight = '$weight',
                  price = $price,
                  calories = '$calories',
                  image_link = '$imageLink'
                  WHERE id_dish = $dishId";
        $this->db->query($query);
        
        // Получение обновленных данных блюда
        $query = "SELECT d.*, c.title as category_title 
                  FROM " . $this->db->escapeIdentifier('dish') . " d
                  JOIN " . $this->db->escapeIdentifier('category') . " c ON d.id_category = c.id_category
                  WHERE d.id_dish = $dishId";
        $result = $this->db->query($query);
        $dishData = $result->fetch_assoc();
        
        // Отправка ответа
        $this->sendSuccess($dishData, 'Блюдо успешно обновлено');
    }
    
    /**
     * Создание нового блюда (только для администраторов)
     */
    public function create() {
        // Проверка авторизации как администратор
        $user = authenticate(true);
        
        // Проверка наличия обязательных полей
        $requiredFields = ['title', 'id_category', 'price'];
        
        foreach ($requiredFields as $field) {
            if (!isset($_POST[$field]) || empty($_POST[$field])) {
                $this->sendError("Поле '$field' обязательно для заполнения", 400);
                return;
            }
        }
        
        $title = $this->db->escape($_POST['title']);
        $categoryId = (int)$_POST['id_category'];
        $compound = isset($_POST['compound']) ? $this->db->escape($_POST['compound']) : '';
        $description = isset($_POST['description']) ? $this->db->escape($_POST['description']) : '';
        $weight = isset($_POST['weight']) ? $this->db->escape($_POST['weight']) : '';
        $price = (float)$_POST['price'];
        $calories = isset($_POST['calories']) ? $this->db->escape($_POST['calories']) : '';
        
        // Проверка существования категории
        $query = "SELECT * FROM " . $this->db->escapeIdentifier('category') . " 
                  WHERE id_category = $categoryId";
        $result = $this->db->query($query);
        
        if ($result->num_rows === 0) {
            $this->sendError('Указанная категория не существует', 404);
            return;
        }
        
        // Обработка загруженного изображения
        $imageLink = '';
        
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $imageLink = uploadImage('image', 'dishes');
            
            if (!$imageLink) {
                $this->sendError('Ошибка загрузки изображения', 400);
                return;
            }
        }
        
        // Создание нового блюда
        $query = "INSERT INTO " . $this->db->escapeIdentifier('dish') . " 
                  (title, id_category, compound, description, weight, price, calories, image_link) 
                  VALUES ('$title', $categoryId, '$compound', '$description', '$weight', $price, '$calories', '$imageLink')";
        $this->db->query($query);
        
        $dishId = $this->db->getLastInsertId();
        
        // Получение данных созданного блюда
        $query = "SELECT d.*, c.title as category_title 
                  FROM " . $this->db->escapeIdentifier('dish') . " d
                  JOIN " . $this->db->escapeIdentifier('category') . " c ON d.id_category = c.id_category
                  WHERE d.id_dish = $dishId";
        $result = $this->db->query($query);
        $dishData = $result->fetch_assoc();
        
        // Отправка ответа
        $this->sendSuccess($dishData, 'Блюдо успешно создано', 201);
    }
} 