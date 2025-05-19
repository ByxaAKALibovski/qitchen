<?php
require_once BASE_PATH . '/controllers/BaseController.php';

class AddressController extends BaseController {
    
    /**
     * Удаление адреса
     * 
     * @param array $params Параметры маршрута
     */
    public function delete($params) {
        // Проверка авторизации
        $user = authenticate();
        
        if (!isset($params['id']) || empty($params['id'])) {
            $this->sendError('Идентификатор адреса не указан', 400);
            return;
        }
        
        $addressId = (int)$params['id'];
        $userId = $user['id'];
        
        // Проверка существования адреса и принадлежности пользователю
        $query = "SELECT * FROM " . $this->db->escapeIdentifier('address') . " 
                  WHERE id_address = $addressId AND id_users = $userId";
        $result = $this->db->query($query);
        
        if ($result->num_rows === 0) {
            $this->sendError('Адрес не найден или не принадлежит пользователю', 404);
            return;
        }
        
        // Удаление адреса
        $query = "DELETE FROM " . $this->db->escapeIdentifier('address') . " 
                  WHERE id_address = $addressId AND id_users = $userId";
        $this->db->query($query);
        
        // Отправка ответа
        $this->sendSuccess(null, 'Адрес успешно удален');
    }
    
    /**
     * Добавление нового адреса
     */
    public function create() {
        // Проверка авторизации
        $user = authenticate();
        $data = getRequestBody();
        
        // Проверка наличия обязательных полей
        if (!$this->validateRequiredFields($data, ['address'])) {
            return;
        }
        
        $userId = $user['id'];
        $address = $this->db->escape($data['address']);
        
        // Добавление адреса
        $query = "INSERT INTO " . $this->db->escapeIdentifier('address') . " (id_users, address) 
                  VALUES ($userId, '$address')";
        $this->db->query($query);
        
        $addressId = $this->db->getLastInsertId();
        
        // Получение добавленного адреса
        $query = "SELECT * FROM " . $this->db->escapeIdentifier('address') . " 
                  WHERE id_address = $addressId";
        $result = $this->db->query($query);
        $addressData = $result->fetch_assoc();
        
        // Отправка ответа
        $this->sendSuccess($addressData, 'Адрес успешно добавлен', 201);
    }
} 