<?php
require_once BASE_PATH . '/controllers/BaseController.php';

class ReservationController extends BaseController {
    
    /**
     * Получение всех бронирований (только для администраторов)
     */
    public function getAll() {
        // Проверка авторизации как администратор
        $user = authenticate(true);
        
        // Получение списка всех бронирований, отсортированных по дате и времени
        $query = "SELECT * FROM " . $this->db->escapeIdentifier('reservation') . " 
                  ORDER BY date ASC, time ASC";
        $result = $this->db->query($query);
        
        $reservations = [];
        while ($reservation = $result->fetch_assoc()) {
            $reservations[] = $reservation;
        }
        
        // Отправка ответа
        $this->sendSuccess($reservations);
    }
    
    /**
     * Создание нового бронирования
     */
    public function create() {
        $data = getRequestBody();
        
        // Проверка наличия обязательных полей
        $requiredFields = ['name', 'phone', 'email', 'count_guest', 'date', 'time'];
        if (!$this->validateRequiredFields($data, $requiredFields)) {
            return;
        }
        
        // Валидация и обработка данных
        $name = $this->db->escape($data['name']);
        $phone = $this->db->escape($data['phone']);
        $email = $this->db->escape($data['email']);
        $countGuest = (int)$data['count_guest'];
        
        // Проверка формата даты и времени
        if (!strtotime($data['date']) || !strtotime($data['time'])) {
            $this->sendError('Неверный формат даты или времени', 400);
            return;
        }
        
        $date = date('Y-m-d', strtotime($data['date']));
        $time = date('H:i:s', strtotime($data['time']));
        
        // Проверка на прошедшую дату
        if (strtotime($date) < strtotime(date('Y-m-d'))) {
            $this->sendError('Нельзя бронировать на прошедшую дату', 400);
            return;
        }
        
        // Создание бронирования
        $query = "INSERT INTO " . $this->db->escapeIdentifier('reservation') . " 
                  (name, phone, email, count_guest, date, time) 
                  VALUES ('$name', '$phone', '$email', $countGuest, '$date', '$time')";
        $this->db->query($query);
        
        $reservationId = $this->db->getLastInsertId();
        
        // Получение данных созданного бронирования
        $query = "SELECT * FROM " . $this->db->escapeIdentifier('reservation') . " 
                  WHERE id_reservation = $reservationId";
        $result = $this->db->query($query);
        $reservationData = $result->fetch_assoc();
        
        // Отправка ответа
        $this->sendSuccess($reservationData, 'Бронирование успешно создано', 201);
    }
} 