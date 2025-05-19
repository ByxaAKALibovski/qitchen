<?php

class ExpertsController {
    /**
     * Получение списка экспертов
     * 
     * @param Request $request Объект запроса
     */
    public function index(Request $request) {
        try {
            $conn = getDbConnection();
            
            $stmt = $conn->query("SELECT * FROM experts ORDER BY FIO");
            $experts = $stmt->fetchAll();
            
            Response::success($experts, 'Список экспертов получен успешно');
        } catch (PDOException $e) {
            Response::error('Ошибка при получении списка экспертов: ' . $e->getMessage());
        }
    }
    
    /**
     * Получение эксперта по ID
     * 
     * @param Request $request Объект запроса
     * @param int $id ID эксперта
     */
    public function show(Request $request, $id) {
        try {
            $conn = getDbConnection();
            
            $stmt = $conn->prepare("SELECT * FROM experts WHERE id_expert = :id");
            $stmt->execute(['id' => $id]);
            
            $expert = $stmt->fetch();
            
            if (!$expert) {
                Response::notFound('Эксперт не найден');
                return;
            }
            
            Response::success($expert, 'Эксперт получен успешно');
        } catch (PDOException $e) {
            Response::error('Ошибка при получении эксперта: ' . $e->getMessage());
        }
    }
    
    /**
     * Добавление нового эксперта (только для администраторов)
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
        if (!isset($data['FIO']) || !isset($data['category_name']) || 
            !isset($data['expert_discription']) || !isset($data['expert_education']) || 
            !isset($data['expert_experience'])) {
            Response::error('Необходимо заполнить все обязательные поля');
            return;
        }
        
        // Загрузка фото
        $photoPath = '';
        
        if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
            $upload = new FileUpload();
            $photoPath = $upload->saveFile('photo', 'experts');
            
            if (!$photoPath) {
                Response::error('Ошибка при загрузке фото');
                return;
            }
        } else {
            Response::error('Фото эксперта обязательно');
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
            
            // Добавление эксперта
            $stmt = $conn->prepare("
                INSERT INTO experts (FIO, category_name, photo, expert_discription, expert_education, expert_experience) 
                VALUES (:FIO, :category_name, :photo, :expert_discription, :expert_education, :expert_experience)
            ");
            
            $stmt->execute([
                'FIO' => $data['FIO'],
                'category_name' => $data['category_name'],
                'photo' => $photoPath,
                'expert_discription' => $data['expert_discription'],
                'expert_education' => $data['expert_education'],
                'expert_experience' => $data['expert_experience']
            ]);
            
            $id = $conn->lastInsertId();
            
            Response::success(['id' => $id], 'Эксперт успешно добавлен');
        } catch (PDOException $e) {
            Response::error('Ошибка при добавлении эксперта: ' . $e->getMessage());
        }
    }
    
    /**
     * Обновление эксперта (только для администраторов)
     * 
     * @param Request $request Объект запроса
     * @param int $id ID эксперта
     */
    public function update(Request $request, $id) {
        // Проверка прав администратора
        if (!AuthMiddleware::admin($request)) {
            return;
        }
        
        $data = $request->all();
        
        // Проверка обязательных полей
        if (!isset($data['FIO']) || !isset($data['category_name']) || 
            !isset($data['expert_discription']) || !isset($data['expert_education']) || 
            !isset($data['expert_experience'])) {
            Response::error('Необходимо заполнить все обязательные поля');
            return;
        }
        
        try {
            $conn = getDbConnection();
            
            // Проверка существования эксперта
            $stmt = $conn->prepare("SELECT * FROM experts WHERE id_expert = :id");
            $stmt->execute(['id' => $id]);
            
            $expert = $stmt->fetch();
            
            if (!$expert) {
                Response::notFound('Эксперт не найден');
                return;
            }
            
            // Проверка существования категории
            $stmt = $conn->prepare("SELECT * FROM category WHERE category_name = :category_name");
            $stmt->execute(['category_name' => $data['category_name']]);
            
            if (!$stmt->fetch()) {
                Response::error('Указанная категория не существует');
                return;
            }
            
            // Обработка фото
            $photoPath = $expert['photo'];
            
            if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
                $upload = new FileUpload();
                $newPhotoPath = $upload->saveFile('photo', 'experts');
                
                if ($newPhotoPath) {
                    // Удаление старого фото
                    if (file_exists(UPLOAD_DIR . $photoPath)) {
                        unlink(UPLOAD_DIR . $photoPath);
                    }
                    
                    $photoPath = $newPhotoPath;
                }
            }
            
            // Обновление эксперта
            $stmt = $conn->prepare("
                UPDATE experts 
                SET FIO = :FIO, 
                    category_name = :category_name, 
                    photo = :photo, 
                    expert_discription = :expert_discription, 
                    expert_education = :expert_education, 
                    expert_experience = :expert_experience 
                WHERE id_expert = :id
            ");
            
            $stmt->execute([
                'FIO' => $data['FIO'],
                'category_name' => $data['category_name'],
                'photo' => $photoPath,
                'expert_discription' => $data['expert_discription'],
                'expert_education' => $data['expert_education'],
                'expert_experience' => $data['expert_experience'],
                'id' => $id
            ]);
            
            Response::success(null, 'Эксперт успешно обновлен');
        } catch (PDOException $e) {
            Response::error('Ошибка при обновлении эксперта: ' . $e->getMessage());
        }
    }
    
    /**
     * Удаление эксперта (только для администраторов)
     * 
     * @param Request $request Объект запроса
     * @param int $id ID эксперта
     */
    public function delete(Request $request, $id) {
        // Проверка прав администратора
        if (!AuthMiddleware::admin($request)) {
            return;
        }
        
        try {
            $conn = getDbConnection();
            
            // Проверка существования эксперта
            $stmt = $conn->prepare("SELECT * FROM experts WHERE id_expert = :id");
            $stmt->execute(['id' => $id]);
            
            $expert = $stmt->fetch();
            
            if (!$expert) {
                Response::notFound('Эксперт не найден');
                return;
            }
            
            // Удаление эксперта
            $stmt = $conn->prepare("DELETE FROM experts WHERE id_expert = :id");
            $stmt->execute(['id' => $id]);
            
            // Удаление фото
            if (!empty($expert['photo']) && file_exists(UPLOAD_DIR . $expert['photo'])) {
                unlink(UPLOAD_DIR . $expert['photo']);
            }
            
            Response::success(null, 'Эксперт успешно удален');
        } catch (PDOException $e) {
            Response::error('Ошибка при удалении эксперта: ' . $e->getMessage());
        }
    }
} 