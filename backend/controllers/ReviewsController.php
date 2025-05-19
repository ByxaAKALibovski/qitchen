<?php

class ReviewsController {
    /**
     * Получение списка всех отзывов
     * 
     * @param Request $request Объект запроса
     */
    public function index(Request $request) {
        try {
            $conn = getDbConnection();
            
            $sql = "SELECT r.*, e.FIO as expert_name 
                   FROM reviews r 
                   LEFT JOIN experts e ON r.id_expert = e.id_expert";
            $params = [];
            
            $stmt = $conn->prepare($sql);
            $stmt->execute($params);
            
            $reviews = $stmt->fetchAll();
            
            Response::success($reviews, 'Список отзывов получен успешно');
        } catch (PDOException $e) {
            Response::error('Ошибка при получении отзывов: ' . $e->getMessage());
        }
    }
    
    /**
     * Получение отзывов по id эксперта
     * 
     * @param Request $request Объект запроса
     * @param int $id_expert ID эксперта
     */
    public function showByExpert(Request $request, $id_expert = null) {
        if (!$id_expert) {
            Response::error('Не указан ID эксперта');
            return;
        }
        
        try {
            $conn = getDbConnection();
            
            $sql = "SELECT r.*, e.FIO as expert_name 
                   FROM reviews r 
                   LEFT JOIN experts e ON r.id_expert = e.id_expert
                   WHERE r.id_expert = :id_expert";
            
            $stmt = $conn->prepare($sql);
            $stmt->execute(['id_expert' => $id_expert]);
            
            $reviews = $stmt->fetchAll();
            
            Response::success($reviews, 'Отзывы по эксперту получены успешно');
        } catch (PDOException $e) {
            Response::error('Ошибка при получении отзывов: ' . $e->getMessage());
        }
    }
    
    /**
     * Добавление нового отзыва
     * 
     * @param Request $request Объект запроса
     */
    public function store(Request $request) {
        $data = $request->all();
        
        // Проверка обязательных полей
        if (!isset($data['phone']) || !isset($data['id_expert'])) {
            Response::error("Поля phone и id_expert обязательны для заполнения");
            return;
        }
        
        // Проверка наличия хотя бы одного типа отзыва
        if (!isset($data['text_positive']) && !isset($data['text_negative'])) {
            Response::error("Необходимо заполнить хотя бы одно поле: text_positive или text_negative");
            return;
        }
        
        // Проверка существования эксперта
        try {
            $conn = getDbConnection();
            
            $stmt = $conn->prepare("SELECT id_expert FROM experts WHERE id_expert = :id_expert");
            $stmt->execute(['id_expert' => $data['id_expert']]);
            
            if (!$stmt->fetch()) {
                Response::error('Эксперт с указанным ID не найден');
                return;
            }
            
            // Добавление отзыва в базу данных
            $stmt = $conn->prepare("
                INSERT INTO reviews (phone, text_positive, text_negative, id_expert) 
                VALUES (:phone, :text_positive, :text_negative, :id_expert)
            ");
            
            $stmt->execute([
                'phone' => $data['phone'],
                'text_positive' => $data['text_positive'] ?? null,
                'text_negative' => $data['text_negative'] ?? null,
                'id_expert' => $data['id_expert']
            ]);
            
            $reviewId = $conn->lastInsertId();
            
            Response::success(['id_reviews' => $reviewId], 'Отзыв успешно добавлен');
        } catch (PDOException $e) {
            Response::error('Ошибка при добавлении отзыва: ' . $e->getMessage());
        }
    }
} 