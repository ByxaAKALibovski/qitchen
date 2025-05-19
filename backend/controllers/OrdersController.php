<?php
require_once BASE_PATH . '/controllers/BaseController.php';

class OrdersController extends BaseController {
    
    /**
     * Создание нового заказа
     */
    public function create() {
        // Проверка авторизации
        $user = authenticate();
        $data = getRequestBody();
        $userId = $user['id'];
        
        // Проверка наличия обязательных полей
        $requiredFields = ['id_address', 'name', 'email', 'phone'];
        if (!$this->validateRequiredFields($data, $requiredFields)) {
            return;
        }
        
        $addressId = (int)$data['id_address'];
        $name = $this->db->escape($data['name']);
        $email = $this->db->escape($data['email']);
        $phone = $this->db->escape($data['phone']);
        
        // Проверка существования адреса и принадлежности пользователю
        $query = "SELECT * FROM " . $this->db->escapeIdentifier('address') . " 
                  WHERE id_address = $addressId AND id_users = $userId";
        $result = $this->db->query($query);
        
        if ($result->num_rows === 0) {
            $this->sendError('Адрес не найден или не принадлежит пользователю', 404);
            return;
        }
        
        // Получение активной корзины пользователя
        $query = "SELECT * FROM " . $this->db->escapeIdentifier('basket') . " 
                  WHERE id_users = $userId AND active = 1";
        $result = $this->db->query($query);
        
        if ($result->num_rows === 0) {
            $this->sendError('Активная корзина не найдена', 404);
            return;
        }
        
        $basket = $result->fetch_assoc();
        $basketId = $basket['id_basket'];
        
        // Проверка наличия товаров в корзине
        $query = "SELECT COUNT(*) as count FROM " . $this->db->escapeIdentifier('basket_list') . " 
                  WHERE id_basket = $basketId";
        $result = $this->db->query($query);
        $count = $result->fetch_assoc()['count'];
        
        if ($count == 0) {
            $this->sendError('Корзина пуста', 400);
            return;
        }
        
        // Расчет общей суммы заказа
        $query = "SELECT SUM(bl.count * d.price) as total 
                  FROM " . $this->db->escapeIdentifier('basket_list') . " bl
                  JOIN " . $this->db->escapeIdentifier('dish') . " d ON bl.id_dish = d.id_dish
                  WHERE bl.id_basket = $basketId";
        $result = $this->db->query($query);
        $totalPrice = $result->fetch_assoc()['total'];
        
        // Создание заказа
        $query = "INSERT INTO " . $this->db->escapeIdentifier('orders') . " 
                  (id_users, id_basket, id_address, name, email, phone, total_price, status) 
                  VALUES ($userId, $basketId, $addressId, '$name', '$email', '$phone', $totalPrice, 1)";
        $this->db->query($query);
        
        $orderId = $this->db->getLastInsertId();
        
        // Пометка корзины как неактивной
        $query = "UPDATE " . $this->db->escapeIdentifier('basket') . " 
                  SET active = 0 WHERE id_basket = $basketId";
        $this->db->query($query);
        
        // Создание новой активной корзины для пользователя
        $query = "INSERT INTO " . $this->db->escapeIdentifier('basket') . " (id_users, active) 
                  VALUES ($userId, 1)";
        $this->db->query($query);
        
        // Получение данных созданного заказа
        $query = "SELECT * FROM " . $this->db->escapeIdentifier('orders') . " 
                  WHERE id_orders = $orderId";
        $result = $this->db->query($query);
        $orderData = $result->fetch_assoc();
        
        // Отправка ответа
        $this->sendSuccess($orderData, 'Заказ успешно создан', 201);
    }
    
    /**
     * Обновление статуса заказа (только для администраторов)
     * 
     * @param array $params Параметры маршрута
     */
    public function update($params) {
        // Проверка авторизации как администратор
        $user = authenticate(true);
        $data = getRequestBody();
        
        if (!isset($params['id']) || empty($params['id'])) {
            $this->sendError('Идентификатор заказа не указан', 400);
            return;
        }
        
        // Проверка наличия обязательных полей
        if (!$this->validateRequiredFields($data, ['status'])) {
            return;
        }
        
        $orderId = (int)$params['id'];
        $status = (int)$data['status'];
        
        // Проверка корректности статуса
        if ($status < 1 || $status > 3) {
            $this->sendError('Некорректный статус заказа', 400);
            return;
        }
        
        // Проверка существования заказа
        $query = "SELECT * FROM " . $this->db->escapeIdentifier('orders') . " 
                  WHERE id_orders = $orderId";
        $result = $this->db->query($query);
        
        if ($result->num_rows === 0) {
            $this->sendError('Заказ не найден', 404);
            return;
        }
        
        // Обновление статуса заказа
        $query = "UPDATE " . $this->db->escapeIdentifier('orders') . " 
                  SET status = $status WHERE id_orders = $orderId";
        $this->db->query($query);
        
        // Отправка ответа
        $this->sendSuccess(null, 'Статус заказа успешно обновлен');
    }
    
    /**
     * Получение всех заказов (только для администраторов)
     */
    public function getAll() {
        // Проверка авторизации как администратор
        $user = authenticate(true);
        
        // Получение всех заказов с дополнительной информацией
        $query = "SELECT o.*, u.email as user_email, a.address,
                  (SELECT JSON_ARRAYAGG(JSON_OBJECT(
                      'id_dish', d.id_dish,
                      'title', d.title,
                      'price', d.price,
                      'count', bl.count,
                      'total', d.price * bl.count
                  ))
                  FROM " . $this->db->escapeIdentifier('basket_list') . " bl
                  JOIN " . $this->db->escapeIdentifier('dish') . " d ON bl.id_dish = d.id_dish
                  WHERE bl.id_basket = o.id_basket) as items
                  FROM " . $this->db->escapeIdentifier('orders') . " o
                  JOIN " . $this->db->escapeIdentifier('users') . " u ON o.id_users = u.id_users
                  JOIN " . $this->db->escapeIdentifier('address') . " a ON o.id_address = a.id_address
                  ORDER BY o.created_at DESC";
        
        $result = $this->db->query($query);
        
        $orders = [];
        while ($order = $result->fetch_assoc()) {
            // Преобразование JSON строки в массив
            $order['items'] = json_decode($order['items'], true);
            $orders[] = $order;
        }
        
        // Отправка ответа
        $this->sendSuccess($orders);
    }
    
    /**
     * Получение заказов текущего пользователя
     */
    public function getMy() {
        // Проверка авторизации
        $user = authenticate();
        $userId = $user['id'];
        
        // Получение заказов пользователя с дополнительной информацией
        $query = "SELECT o.*, a.address,
                  (SELECT JSON_ARRAYAGG(JSON_OBJECT(
                      'id_dish', d.id_dish,
                      'title', d.title,
                      'price', d.price,
                      'count', bl.count,
                      'total', d.price * bl.count
                  ))
                  FROM " . $this->db->escapeIdentifier('basket_list') . " bl
                  JOIN " . $this->db->escapeIdentifier('dish') . " d ON bl.id_dish = d.id_dish
                  WHERE bl.id_basket = o.id_basket) as items
                  FROM " . $this->db->escapeIdentifier('orders') . " o
                  JOIN " . $this->db->escapeIdentifier('address') . " a ON o.id_address = a.id_address
                  WHERE o.id_users = $userId
                  ORDER BY o.created_at DESC";
        
        $result = $this->db->query($query);
        
        $orders = [];
        while ($order = $result->fetch_assoc()) {
            // Преобразование JSON строки в массив
            $order['items'] = json_decode($order['items'], true);
            $orders[] = $order;
        }
        
        // Отправка ответа
        $this->sendSuccess($orders);
    }
} 