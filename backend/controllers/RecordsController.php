<?php

class RecordsController {
    /**
     * Получение списка записей на приём (только для администраторов)
     * 
     * @param Request $request Объект запроса
     */
    public function index(Request $request) {
        // Проверка прав администратора
        if (!AuthMiddleware::admin($request)) {
            return;
        }
        
        try {
            $conn = getDbConnection();
            
            $stmt = $conn->query("SELECT * FROM records ORDER BY date DESC, time DESC");
            $records = $stmt->fetchAll();
            
            Response::success($records, 'Список записей получен успешно');
        } catch (PDOException $e) {
            Response::error('Ошибка при получении списка записей: ' . $e->getMessage());
        }
    }
    
    /**
     * Получение записи по ID (только для администраторов)
     * 
     * @param Request $request Объект запроса
     * @param int $id ID записи
     */
    public function show(Request $request, $id) {
        // Проверка прав администратора
        if (!AuthMiddleware::admin($request)) {
            return;
        }
        
        try {
            $conn = getDbConnection();
            
            $stmt = $conn->prepare("SELECT * FROM records WHERE id_records = :id");
            $stmt->execute(['id' => $id]);
            
            $record = $stmt->fetch();
            
            if (!$record) {
                Response::notFound('Запись не найдена');
                return;
            }
            
            Response::success($record, 'Запись получена успешно');
        } catch (PDOException $e) {
            Response::error('Ошибка при получении записи: ' . $e->getMessage());
        }
    }
    
    /**
     * Создание новой записи на приём (только для администраторов)
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
        if (!isset($data['fio_user']) || !isset($data['phone_user']) || 
            !isset($data['expert']) || !isset($data['date']) || !isset($data['time'])) {
            Response::error('Все поля обязательны для заполнения');
            return;
        }
        
        try {
            $conn = getDbConnection();
            
            // Проверка на существование записи в это время
            $stmt = $conn->prepare("
                SELECT * FROM records 
                WHERE expert = :expert AND date = :date AND time = :time
            ");
            
            $stmt->execute([
                'expert' => $data['expert'],
                'date' => $data['date'],
                'time' => $data['time']
            ]);
            
            if ($stmt->fetch()) {
                Response::error('На это время уже существует запись');
                return;
            }
            
            // Создание записи
            $stmt = $conn->prepare("
                INSERT INTO records (fio_user, phone_user, expert, date, time) 
                VALUES (:fio_user, :phone_user, :expert, :date, :time)
            ");
            
            $stmt->execute([
                'fio_user' => $data['fio_user'],
                'phone_user' => $data['phone_user'],
                'expert' => $data['expert'],
                'date' => $data['date'],
                'time' => $data['time']
            ]);
            
            $id = $conn->lastInsertId();
            
            Response::success(['id' => $id], 'Запись успешно создана');
        } catch (PDOException $e) {
            Response::error('Ошибка при создании записи: ' . $e->getMessage());
        }
    }
    
    /**
     * Обновление записи на приём (только для администраторов)
     * 
     * @param Request $request Объект запроса
     * @param int $id ID записи
     */
    public function update(Request $request, $id) {
        // Проверка прав администратора
        if (!AuthMiddleware::admin($request)) {
            return;
        }
        
        $data = $request->all();
        
        // Проверка обязательных полей
        if (!isset($data['fio_user']) || !isset($data['phone_user']) || 
            !isset($data['expert']) || !isset($data['date']) || !isset($data['time'])) {
            Response::error('Все поля обязательны для заполнения');
            return;
        }
        
        try {
            $conn = getDbConnection();
            
            // Проверка существования записи
            $stmt = $conn->prepare("SELECT * FROM records WHERE id_records = :id");
            $stmt->execute(['id' => $id]);
            
            if (!$stmt->fetch()) {
                Response::notFound('Запись не найдена');
                return;
            }
            
            // Проверка на существование другой записи в это время
            $stmt = $conn->prepare("
                SELECT * FROM records 
                WHERE expert = :expert AND date = :date AND time = :time AND id_records != :id
            ");
            
            $stmt->execute([
                'expert' => $data['expert'],
                'date' => $data['date'],
                'time' => $data['time'],
                'id' => $id
            ]);
            
            if ($stmt->fetch()) {
                Response::error('На это время уже существует другая запись');
                return;
            }
            
            // Обновление записи
            $stmt = $conn->prepare("
                UPDATE records 
                SET fio_user = :fio_user, 
                    phone_user = :phone_user, 
                    expert = :expert, 
                    date = :date, 
                    time = :time 
                WHERE id_records = :id
            ");
            
            $stmt->execute([
                'fio_user' => $data['fio_user'],
                'phone_user' => $data['phone_user'],
                'expert' => $data['expert'],
                'date' => $data['date'],
                'time' => $data['time'],
                'id' => $id
            ]);
            
            Response::success(null, 'Запись успешно обновлена');
        } catch (PDOException $e) {
            Response::error('Ошибка при обновлении записи: ' . $e->getMessage());
        }
    }
    
    /**
     * Удаление записи на приём (только для администраторов)
     * 
     * @param Request $request Объект запроса
     * @param int $id ID записи
     */
    public function delete(Request $request, $id) {
        // Проверка прав администратора
        if (!AuthMiddleware::admin($request)) {
            return;
        }
        
        try {
            $conn = getDbConnection();
            
            // Проверка существования записи
            $stmt = $conn->prepare("SELECT * FROM records WHERE id_records = :id");
            $stmt->execute(['id' => $id]);
            
            if (!$stmt->fetch()) {
                Response::notFound('Запись не найдена');
                return;
            }
            
            // Удаление записи
            $stmt = $conn->prepare("DELETE FROM records WHERE id_records = :id");
            $stmt->execute(['id' => $id]);
            
            Response::success(null, 'Запись успешно удалена');
        } catch (PDOException $e) {
            Response::error('Ошибка при удалении записи: ' . $e->getMessage());
        }
    }
}