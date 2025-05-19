<?php
require_once BASE_PATH . '/controllers/BaseController.php';

class BasketController extends BaseController {
    
    /**
     * Получение активной корзины пользователя со списком блюд
     */
    public function getBasket() {
        // Проверка авторизации
        $user = authenticate();
        $userId = $user['id'];
        
        // Получение активной корзины пользователя
        $query = "SELECT * FROM " . $this->db->escapeIdentifier('basket') . " 
                  WHERE id_users = $userId AND active = 1";
        $result = $this->db->query($query);
        
        // Если корзина не найдена, создаем новую
        if ($result->num_rows === 0) {
            $query = "INSERT INTO " . $this->db->escapeIdentifier('basket') . " (id_users, active) 
                      VALUES ($userId, 1)";
            $this->db->query($query);
            
            $basketId = $this->db->getLastInsertId();
        } else {
            $basket = $result->fetch_assoc();
            $basketId = $basket['id_basket'];
        }
        
        // Получение содержимого корзины
        $query = "SELECT bl.*, d.title, d.price, d.image_link, c.title as category_title 
                  FROM " . $this->db->escapeIdentifier('basket_list') . " bl
                  JOIN " . $this->db->escapeIdentifier('dish') . " d ON bl.id_dish = d.id_dish
                  JOIN " . $this->db->escapeIdentifier('category') . " c ON d.id_category = c.id_category
                  WHERE bl.id_basket = $basketId";
        $result = $this->db->query($query);
        
        $items = [];
        $totalSum = 0;
        
        while ($item = $result->fetch_assoc()) {
            $itemTotal = $item['price'] * $item['count'];
            $totalSum += $itemTotal;
            
            $item['total'] = $itemTotal;
            $items[] = $item;
        }
        
        // Формирование ответа
        $basketData = [
            'id_basket' => $basketId,
            'items' => $items,
            'total_sum' => $totalSum,
            'count_items' => count($items)
        ];
        
        // Отправка ответа
        $this->sendSuccess($basketData);
    }
    
    /**
     * Удаление блюда из корзины
     * 
     * @param array $params Параметры маршрута
     */
    public function deleteDish($params) {
        // Проверка авторизации
        $user = authenticate();
        
        if (!isset($params['id']) || empty($params['id'])) {
            $this->sendError('Идентификатор блюда не указан', 400);
            return;
        }
        
        $dishId = (int)$params['id'];
        $userId = $user['id'];
        
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
        
        // Удаление блюда из корзины
        $query = "DELETE FROM " . $this->db->escapeIdentifier('basket_list') . " 
                  WHERE id_basket = $basketId AND id_dish = $dishId";
        $this->db->query($query);
        
        // Отправка ответа
        $this->sendSuccess(null, 'Блюдо успешно удалено из корзины');
    }
    
    /**
     * Обновление количества блюда в корзине
     * 
     * @param array $params Параметры маршрута
     */
    public function updateDish($params) {
        // Проверка авторизации
        $user = authenticate();
        $data = getRequestBody();
        
        if (!isset($params['id']) || empty($params['id'])) {
            $this->sendError('Идентификатор блюда не указан', 400);
            return;
        }
        
        // Проверка наличия обязательных полей
        if (!$this->validateRequiredFields($data, ['count'])) {
            return;
        }
        
        $dishId = (int)$params['id'];
        $count = (int)$data['count'];
        $userId = $user['id'];
        
        if ($count <= 0) {
            $this->sendError('Количество должно быть положительным числом', 400);
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
        
        // Проверка наличия блюда в корзине
        $query = "SELECT * FROM " . $this->db->escapeIdentifier('basket_list') . " 
                  WHERE id_basket = $basketId AND id_dish = $dishId";
        $result = $this->db->query($query);
        
        if ($result->num_rows === 0) {
            $this->sendError('Блюдо не найдено в корзине', 404);
            return;
        }
        
        // Обновление количества
        $query = "UPDATE " . $this->db->escapeIdentifier('basket_list') . " 
                  SET count = $count WHERE id_basket = $basketId AND id_dish = $dishId";
        $this->db->query($query);
        
        // Отправка ответа
        $this->sendSuccess(null, 'Количество блюда успешно обновлено');
    }
    
    /**
     * Добавление блюда в корзину
     */
    public function addDish() {
        // Проверка авторизации
        $user = authenticate();
        $data = getRequestBody();
        
        // Проверка наличия обязательных полей
        if (!$this->validateRequiredFields($data, ['id_dish'])) {
            return;
        }
        
        $dishId = (int)$data['id_dish'];
        $count = isset($data['count']) ? (int)$data['count'] : 1;
        $userId = $user['id'];
        
        if ($count <= 0) {
            $this->sendError('Количество должно быть положительным числом', 400);
            return;
        }
        
        // Проверка существования блюда
        $query = "SELECT * FROM " . $this->db->escapeIdentifier('dish') . " 
                  WHERE id_dish = $dishId";
        $result = $this->db->query($query);
        
        if ($result->num_rows === 0) {
            $this->sendError('Блюдо не найдено', 404);
            return;
        }
        
        // Получение активной корзины пользователя
        $query = "SELECT * FROM " . $this->db->escapeIdentifier('basket') . " 
                  WHERE id_users = $userId AND active = 1";
        $result = $this->db->query($query);
        
        // Если корзина не найдена, создаем новую
        if ($result->num_rows === 0) {
            $query = "INSERT INTO " . $this->db->escapeIdentifier('basket') . " (id_users, active) 
                      VALUES ($userId, 1)";
            $this->db->query($query);
            
            $basketId = $this->db->getLastInsertId();
        } else {
            $basket = $result->fetch_assoc();
            $basketId = $basket['id_basket'];
        }
        
        // Проверка, есть ли уже такое блюдо в корзине
        $query = "SELECT * FROM " . $this->db->escapeIdentifier('basket_list') . " 
                  WHERE id_basket = $basketId AND id_dish = $dishId";
        $result = $this->db->query($query);
        
        if ($result->num_rows > 0) {
            // Если блюдо уже есть, увеличиваем количество
            $item = $result->fetch_assoc();
            $newCount = $item['count'] + $count;
            
            $query = "UPDATE " . $this->db->escapeIdentifier('basket_list') . " 
                      SET count = $newCount WHERE id_basket_list = " . $item['id_basket_list'];
            $this->db->query($query);
            
            $message = 'Количество блюда в корзине увеличено';
        } else {
            // Если блюда нет, добавляем его
            $query = "INSERT INTO " . $this->db->escapeIdentifier('basket_list') . " (id_basket, id_dish, count) 
                      VALUES ($basketId, $dishId, $count)";
            $this->db->query($query);
            
            $message = 'Блюдо успешно добавлено в корзину';
        }
        
        // Отправка ответа
        $this->sendSuccess(null, $message, 201);
    }
} 