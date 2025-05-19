<?php

class CategoryController {
    /**
     * Получение списка всех категорий
     * 
     * @param Request $request Объект запроса
     */
    public function index(Request $request) {
        try {
            $conn = getDbConnection();
            
            $stmt = $conn->query("SELECT * FROM category ORDER BY category_name");
            $categories = $stmt->fetchAll();
            
            Response::success($categories, 'Список категорий получен успешно');
        } catch (PDOException $e) {
            Response::error('Ошибка при получении категорий: ' . $e->getMessage());
        }
    }
    
    /**
     * Получение одной категории по ID
     * 
     * @param Request $request Объект запроса
     * @param int $id ID категории
     */
    public function show(Request $request, $id) {
        try {
            $conn = getDbConnection();
            
            $stmt = $conn->prepare("SELECT * FROM category WHERE id_category = :id");
            $stmt->execute(['id' => $id]);
            
            $category = $stmt->fetch();
            
            if (!$category) {
                Response::notFound('Категория не найдена');
                return;
            }
            
            Response::success($category, 'Категория получена успешно');
        } catch (PDOException $e) {
            Response::error('Ошибка при получении категории: ' . $e->getMessage());
        }
    }
    
    /**
     * Добавление новой категории (только для администраторов)
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
        if (!isset($data['category_name']) || !isset($data['description'])) {
            Response::error($data);
            return;
            Response::error('Название и описание категории обязательны для заполнения');
            return;
        }
        
        // Загрузка изображения
        $photoPath = '';
        
        if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
            $upload = new FileUpload();
            $photoPath = $upload->saveFile('photo', 'categories');
            
            if (!$photoPath) {
                Response::error('Ошибка при загрузке фото');
                return;
            }
        } else {
            Response::error('Фото категории обязательно');
            return;
        }
        
        try {
            $conn = getDbConnection();
            
            // Проверка, не существует ли уже категории с таким названием
            $stmt = $conn->prepare("SELECT id_category FROM category WHERE category_name = :category_name");
            $stmt->execute(['category_name' => $data['category_name']]);
            
            if ($stmt->fetch()) {
                Response::error('Категория с таким названием уже существует');
                return;
            }
            
            // Добавление категории в базу данных
            $stmt = $conn->prepare("
                INSERT INTO category (category_name, description, photo) 
                VALUES (:category_name, :description, :photo)
            ");
            
            $stmt->execute([
                'category_name' => $data['category_name'],
                'description' => $data['description'],
                'photo' => $photoPath
            ]);
            
            $categoryId = $conn->lastInsertId();
            
            Response::success(['id_category' => $categoryId], 'Категория успешно добавлена');
        } catch (PDOException $e) {
            Response::error('Ошибка при добавлении категории: ' . $e->getMessage());
        }
    }
    
    /**
     * Редактирование категории (только для администраторов)
     * 
     * @param Request $request Объект запроса
     * @param int $id ID категории
     */
    public function update(Request $request, $id) {
        // Проверка прав администратора
        if (!AuthMiddleware::admin($request)) {
            return;
        }
        
        $data = $request->all();
        
        // Проверка наличия данных для обновления
        if (empty($data) && !isset($_FILES['photo'])) {
            Response::error('Нет данных для обновления');
            return;
        }
        
        try {
            $conn = getDbConnection();
            
            // Проверка существования категории
            $stmt = $conn->prepare("SELECT * FROM category WHERE id_category = :id");
            $stmt->execute(['id' => $id]);
            
            $category = $stmt->fetch();
            
            if (!$category) {
                Response::notFound('Категория не найдена');
                return;
            }
            
            // Обновление полей категории
            $fields = [];
            $params = ['id' => $id];
            
            if (isset($data['category_name'])) {
                // Проверка на дубликат при изменении названия
                if ($data['category_name'] !== $category['category_name']) {
                    $stmt = $conn->prepare("
                        SELECT id_category FROM category 
                        WHERE category_name = :category_name AND id_category != :id
                    ");
                    $stmt->execute([
                        'category_name' => $data['category_name'],
                        'id' => $id
                    ]);
                    
                    if ($stmt->fetch()) {
                        Response::error('Категория с таким названием уже существует');
                        return;
                    }
                }
                
                $fields[] = "category_name = :category_name";
                $params['category_name'] = $data['category_name'];
            }
            
            if (isset($data['description'])) {
                $fields[] = "description = :description";
                $params['description'] = $data['description'];
            }
            
            // Обработка фото
            if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
                $upload = new FileUpload();
                $newPhotoPath = $upload->saveFile('photo', 'categories');
                
                if ($newPhotoPath) {
                    $fields[] = "photo = :photo";
                    $params['photo'] = $newPhotoPath;
                    
                    // Удаление старого фото
                    if (!empty($category['photo']) && file_exists(UPLOAD_DIR . $category['photo'])) {
                        unlink(UPLOAD_DIR . $category['photo']);
                    }
                } else {
                    Response::error('Ошибка при загрузке фото');
                    return;
                }
            }
            
            if (empty($fields)) {
                Response::error('Нет данных для обновления');
                return;
            }
            
            // Выполнение запроса обновления
            $sql = "UPDATE category SET " . implode(", ", $fields) . " WHERE id_category = :id";
            
            $stmt = $conn->prepare($sql);
            $stmt->execute($params);
            
            Response::success(null, 'Категория успешно обновлена');
        } catch (PDOException $e) {
            Response::error('Ошибка при обновлении категории: ' . $e->getMessage());
        }
    }
    
    /**
     * Удаление категории (только для администраторов)
     * 
     * @param Request $request Объект запроса
     * @param int $id ID категории
     */
    public function delete(Request $request, $id) {
        // Проверка прав администратора
        if (!AuthMiddleware::admin($request)) {
            return;
        }
        
        try {
            $conn = getDbConnection();
            
            // Получение категории для удаления фото
            $stmt = $conn->prepare("SELECT photo FROM category WHERE id_category = :id");
            $stmt->execute(['id' => $id]);
            
            $category = $stmt->fetch();
            
            if (!$category) {
                Response::notFound('Категория не найдена');
                return;
            }
            
            // Проверка, используется ли категория в экспертах
            $stmt = $conn->prepare("SELECT id_expert FROM experts WHERE category_name = :category_name LIMIT 1");
            $stmt->execute(['category_name' => $category['category_name']]);
            
            if ($stmt->fetch()) {
                Response::error('Невозможно удалить категорию, так как она используется экспертами');
                return;
            }
            
            // Проверка, используется ли категория в услугах
            $stmt = $conn->prepare("SELECT id_services FROM services WHERE category_name = :category_name LIMIT 1");
            $stmt->execute(['category_name' => $category['category_name']]);
            
            if ($stmt->fetch()) {
                Response::error('Невозможно удалить категорию, так как она используется в услугах');
                return;
            }
            
            // Удаление категории из базы данных
            $stmt = $conn->prepare("DELETE FROM category WHERE id_category = :id");
            $stmt->execute(['id' => $id]);
            
            // Удаление фото
            if (!empty($category['photo']) && file_exists(UPLOAD_DIR . $category['photo'])) {
                unlink(UPLOAD_DIR . $category['photo']);
            }
            
            Response::success(null, 'Категория успешно удалена');
        } catch (PDOException $e) {
            Response::error('Ошибка при удалении категории: ' . $e->getMessage());
        }
    }
} 