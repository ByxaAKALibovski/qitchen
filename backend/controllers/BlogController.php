<?php
require_once BASE_PATH . '/controllers/BaseController.php';

class BlogController extends BaseController {
    
    /**
     * Получение одной записи блога по ID
     * 
     * @param array $params Параметры маршрута
     */
    public function getOne($params) {
        if (!isset($params['id']) || empty($params['id'])) {
            $this->sendError('Идентификатор записи не указан', 400);
            return;
        }
        
        $blogId = (int)$params['id'];
        
        // Получение данных записи блога
        $query = "SELECT * FROM " . $this->db->escapeIdentifier('blog') . " 
                  WHERE id_blog = $blogId";
        $result = $this->db->query($query);
        
        if ($result->num_rows === 0) {
            $this->sendError('Запись блога не найдена', 404);
            return;
        }
        
        $blog = $result->fetch_assoc();
        
        // Отправка ответа
        $this->sendSuccess($blog);
    }
    
    /**
     * Получение всех записей блога
     */
    public function getAll() {
        // Получение всех записей блога, отсортированных по дате создания (новые сверху)
        $query = "SELECT * FROM " . $this->db->escapeIdentifier('blog') . " 
                  ORDER BY created_at DESC";
        $result = $this->db->query($query);
        
        $blogs = [];
        while ($blog = $result->fetch_assoc()) {
            $blogs[] = $blog;
        }
        
        // Отправка ответа
        $this->sendSuccess($blogs);
    }
    
    /**
     * Удаление записи блога (только для администраторов)
     * 
     * @param array $params Параметры маршрута
     */
    public function delete($params) {
        // Проверка авторизации как администратор
        $user = authenticate(true);
        
        if (!isset($params['id']) || empty($params['id'])) {
            $this->sendError('Идентификатор записи не указан', 400);
            return;
        }
        
        $blogId = (int)$params['id'];
        
        // Проверка существования записи
        $query = "SELECT * FROM " . $this->db->escapeIdentifier('blog') . " 
                  WHERE id_blog = $blogId";
        $result = $this->db->query($query);
        
        if ($result->num_rows === 0) {
            $this->sendError('Запись блога не найдена', 404);
            return;
        }
        
        $blog = $result->fetch_assoc();
        $imagePath = $blog['image_link'];
        
        // Удаление изображения, если оно есть
        if (!empty($imagePath) && file_exists(BASE_PATH . '/' . $imagePath)) {
            unlink(BASE_PATH . '/' . $imagePath);
        }
        
        // Удаление записи
        $query = "DELETE FROM " . $this->db->escapeIdentifier('blog') . " 
                  WHERE id_blog = $blogId";
        $this->db->query($query);
        
        // Отправка ответа
        $this->sendSuccess(null, 'Запись блога успешно удалена');
    }
    
    /**
     * Обновление записи блога (только для администраторов)
     * 
     * @param array $params Параметры маршрута
     */
    public function update($params) {
        // Проверка авторизации как администратор
        $user = authenticate(true);
        
        if (!isset($params['id']) || empty($params['id'])) {
            $this->sendError('Идентификатор записи не указан', 400);
            return;
        }
        
        $blogId = (int)$params['id'];
        
        // Проверка существования записи
        $query = "SELECT * FROM " . $this->db->escapeIdentifier('blog') . " 
                  WHERE id_blog = $blogId";
        $result = $this->db->query($query);
        
        if ($result->num_rows === 0) {
            $this->sendError('Запись блога не найдена', 404);
            return;
        }
        
        $blog = $result->fetch_assoc();
        $oldImagePath = $blog['image_link'];
        
        // Получение данных формы
        $title = isset($_POST['title']) ? $this->db->escape($_POST['title']) : $blog['title'];
        $textPrev = isset($_POST['text_prev']) ? $this->db->escape($_POST['text_prev']) : $blog['text_prev'];
        $description = isset($_POST['description']) ? $this->db->escape($_POST['description']) : $blog['description'];
        
        // Обработка загруженного изображения
        $imageLink = $oldImagePath;
        
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $newImagePath = uploadImage('image', 'blog');
            
            if ($newImagePath) {
                $imageLink = $newImagePath;
                
                // Удаление старого изображения, если оно есть
                if (!empty($oldImagePath) && file_exists(BASE_PATH . '/' . $oldImagePath)) {
                    unlink(BASE_PATH . '/' . $oldImagePath);
                }
            }
        }
        
        // Обновление записи блога
        $query = "UPDATE " . $this->db->escapeIdentifier('blog') . " SET 
                  title = '$title',
                  text_prev = '$textPrev',
                  description = '$description',
                  image_link = '$imageLink'
                  WHERE id_blog = $blogId";
        $this->db->query($query);
        
        // Получение обновленных данных записи
        $query = "SELECT * FROM " . $this->db->escapeIdentifier('blog') . " 
                  WHERE id_blog = $blogId";
        $result = $this->db->query($query);
        $blogData = $result->fetch_assoc();
        
        // Отправка ответа
        $this->sendSuccess($blogData, 'Запись блога успешно обновлена');
    }
    
    /**
     * Создание новой записи блога (только для администраторов)
     */
    public function create() {
        // Проверка авторизации как администратор
        $user = authenticate(true);
        
        // Проверка наличия обязательных полей
        $requiredFields = ['title', 'text_prev', 'description'];
        
        foreach ($requiredFields as $field) {
            if (!isset($_POST[$field]) || empty($_POST[$field])) {
                $this->sendError("Поле '$field' обязательно для заполнения", 400);
                return;
            }
        }
        
        $title = $this->db->escape($_POST['title']);
        $textPrev = $this->db->escape($_POST['text_prev']);
        $description = $this->db->escape($_POST['description']);
        
        // Обработка загруженного изображения
        $imageLink = '';
        
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $imageLink = uploadImage('image', 'blog');
            
            if (!$imageLink) {
                $this->sendError('Ошибка загрузки изображения', 400);
                return;
            }
        }
        
        // Создание новой записи блога
        $query = "INSERT INTO " . $this->db->escapeIdentifier('blog') . " 
                  (title, text_prev, description, image_link) 
                  VALUES ('$title', '$textPrev', '$description', '$imageLink')";
        $this->db->query($query);
        
        $blogId = $this->db->getLastInsertId();
        
        // Получение данных созданной записи
        $query = "SELECT * FROM " . $this->db->escapeIdentifier('blog') . " 
                  WHERE id_blog = $blogId";
        $result = $this->db->query($query);
        $blogData = $result->fetch_assoc();
        
        // Отправка ответа
        $this->sendSuccess($blogData, 'Запись блога успешно создана', 201);
    }
} 