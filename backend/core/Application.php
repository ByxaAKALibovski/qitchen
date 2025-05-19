<?php
require_once 'Database.php';
require_once 'JWT.php';

class Application {
    private $router;
    private $db;
    
    public function __construct() {
        // Инициализация маршрутизатора
        $this->router = new Router();
        
        // Инициализация соединения с базой данных
        $this->db = Database::getInstance();
        
        // Создание необходимых директорий
        $this->createDirectories();
        
        // Загрузка маршрутов
        $this->loadRoutes();
    }
    
    /**
     * Создание необходимых директорий
     */
    private function createDirectories() {
        // Директория для загрузки файлов
        if (!file_exists(UPLOAD_DIR)) {
            mkdir(UPLOAD_DIR, 0777, true);
        }
        
        // Директория для изображений
        if (!file_exists(IMAGES_DIR)) {
            mkdir(IMAGES_DIR, 0777, true);
        }
    }
    
    /**
     * Загрузка всех маршрутов приложения
     */
    private function loadRoutes() {
        // Базовый маршрут для проверки работоспособности API
        $this->router->get('/', function() {
            return json_encode([
                'status' => 'success',
                'message' => 'Qitchen API работает!'
            ]);
        });
        
        // Маршруты пользователей
        $this->router->post('/users/login', 'Users@login');
        $this->router->post('/users/register', 'Users@register');
        $this->router->get('/users/profile', 'Users@profile');
        $this->router->post('/users/update-password', 'Users@updatePassword');
        
        // Маршруты адресов
        $this->router->delete('/address/{id}', 'Address@delete');
        $this->router->post('/address', 'Address@create');
        
        // Маршруты корзины
        $this->router->get('/basket', 'Basket@getBasket');
        $this->router->delete('/basket/dish/{id}', 'Basket@deleteDish');
        $this->router->put('/basket/dish/{id}', 'Basket@updateDish');
        $this->router->post('/basket/dish', 'Basket@addDish');
        
        // Маршруты заказов
        $this->router->post('/orders', 'Orders@create');
        $this->router->put('/orders/{id}', 'Orders@update');
        $this->router->get('/orders', 'Orders@getAll');
        $this->router->get('/my-orders', 'Orders@getMy');
        
        // Маршруты бронирования
        $this->router->get('/reservation', 'Reservation@getAll');
        $this->router->post('/reservation', 'Reservation@create');
        
        // Маршруты категорий
        $this->router->get('/category/{id}', 'Category@getOne');
        $this->router->get('/category', 'Category@getAll');
        $this->router->put('/category/{id}', 'Category@update');
        $this->router->delete('/category/{id}', 'Category@delete');
        $this->router->post('/category', 'Category@create');
        
        // Маршруты блюд
        $this->router->get('/product/{id}', 'Dish@getOne');
        $this->router->get('/product', 'Dish@getAll');
        $this->router->delete('/product/{id}', 'Dish@delete');
        $this->router->post('/product/{id}', 'Dish@update');
        $this->router->post('/product', 'Dish@create');
        
        // Маршруты блога
        $this->router->get('/blog/{id}', 'Blog@getOne');
        $this->router->get('/blog', 'Blog@getAll');
        $this->router->delete('/blog/{id}', 'Blog@delete');
        $this->router->post('/blog/{id}', 'Blog@update');
        $this->router->post('/blog', 'Blog@create');
        
        // Обработка 404
        $this->router->notFound(function() {
            header("HTTP/1.1 404 Not Found");
            return json_encode([
                'status' => 'error',
                'message' => 'Маршрут не найден'
            ]);
        });
    }
    
    /**
     * Запуск приложения
     */
    public function run() {
        // Запуск маршрутизатора
        $this->router->run();
    }
} 