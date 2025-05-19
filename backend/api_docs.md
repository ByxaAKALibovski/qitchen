# Qitchen API - Документация

## Общая информация

API построено с использованием REST архитектуры и обменивается данными в формате JSON.

### Базовый URL

```
http://localhost/qitchen/backend
```

### Аутентификация

Для защищенных маршрутов используется JWT (JSON Web Token) аутентификация. 
Токен необходимо передавать в заголовке `Authorization` в формате:

```
Authorization: Bearer {token}
```

### Формат ответа

Все ответы имеют единый формат:

Успешный ответ:
```json
{
  "status": "success",
  "message": "Сообщение об успехе",
  "data": { /* Данные ответа */ }
}
```

Ответ с ошибкой:
```json
{
  "status": "error",
  "message": "Сообщение об ошибке"
}
```

## Пользователи (Users)

### Вход пользователя

**Запрос:**
- Метод: `POST`
- Путь: `/users/login`
- Тело запроса:
  ```json
  {
    "email": "user@example.com",
    "password": "password"
  }
  ```

**Ответ:**
```json
{
  "status": "success",
  "message": "Вход выполнен успешно",
  "data": {
    "token": "JWT_TOKEN",
    "profile_data": {
      "id": 1,
      "email": "user@example.com",
      "op": 2
    }
  }
}
```

### Регистрация пользователя

**Запрос:**
- Метод: `POST`
- Путь: `/users/register`
- Тело запроса:
  ```json
  {
    "email": "user@example.com",
    "password": "password"
  }
  ```

**Ответ:**
```json
{
  "status": "success",
  "message": "Регистрация выполнена успешно",
  "data": {
    "token": "JWT_TOKEN",
    "profile_data": {
      "id": 1,
      "email": "user@example.com",
      "op": 2
    }
  }
}
```

### Получение профиля пользователя

**Запрос:**
- Метод: `GET`
- Путь: `/users/profile`
- Заголовок: `Authorization: Bearer {token}`

**Ответ:**
```json
{
  "status": "success",
  "message": "Операция выполнена успешно",
  "data": {
    "user": {
      "id_users": 1,
      "email": "user@example.com",
      "op": 2
    },
    "addresses": [
      {
        "id_address": 1,
        "id_users": 1,
        "address": "ул. Примерная, д. 1, кв. 1",
        "created_at": "2023-05-01 12:00:00"
      }
    ]
  }
}
```

### Обновление пароля

**Запрос:**
- Метод: `POST`
- Путь: `/users/update-password`
- Заголовок: `Authorization: Bearer {token}`
- Тело запроса:
  ```json
  {
    "password": "current_password",
    "new_password": "new_password"
  }
  ```

**Ответ:**
```json
{
  "status": "success",
  "message": "Пароль успешно обновлен"
}
```

## Адреса (Address)

### Создание адреса

**Запрос:**
- Метод: `POST`
- Путь: `/address`
- Заголовок: `Authorization: Bearer {token}`
- Тело запроса:
  ```json
  {
    "address": "ул. Примерная, д. 1, кв. 1"
  }
  ```

**Ответ:**
```json
{
  "status": "success",
  "message": "Адрес успешно добавлен",
  "data": {
    "id": 1,
    "address": "ул. Примерная, д. 1, кв. 1"
  }
}
```

### Удаление адреса

**Запрос:**
- Метод: `DELETE`
- Путь: `/address/{id}`
- Заголовок: `Authorization: Bearer {token}`

**Ответ:**
```json
{
  "status": "success",
  "message": "Адрес успешно удален"
}
```

## Корзина (Basket)

### Получение содержимого корзины

**Запрос:**
- Метод: `GET`
- Путь: `/basket`
- Заголовок: `Authorization: Bearer {token}`

**Ответ:**
```json
{
  "status": "success",
  "message": "Операция выполнена успешно",
  "data": {
    "basket_id": 1,
    "items": [
      {
        "id_basket_item": 1,
        "id_basket": 1,
        "id_dish": 5,
        "quantity": 2,
        "dish": {
          "id_dish": 5,
          "name": "Пицца Маргарита",
          "description": "Классическая итальянская пицца",
          "price": 500,
          "image": "uploads/images/pizza.jpg",
          "id_category": 2
        }
      }
    ],
    "total": 1000
  }
}
```

### Добавление блюда в корзину

**Запрос:**
- Метод: `POST`
- Путь: `/basket/dish`
- Заголовок: `Authorization: Bearer {token}`
- Тело запроса:
  ```json
  {
    "id_dish": 5,
    "quantity": 2
  }
  ```

**Ответ:**
```json
{
  "status": "success",
  "message": "Блюдо добавлено в корзину",
  "data": {
    "id": 1,
    "quantity": 2
  }
}
```

### Обновление количества блюда в корзине

**Запрос:**
- Метод: `PUT`
- Путь: `/basket/dish/{id}`
- Заголовок: `Authorization: Bearer {token}`
- Тело запроса:
  ```json
  {
    "quantity": 3
  }
  ```

**Ответ:**
```json
{
  "status": "success",
  "message": "Количество блюда обновлено",
  "data": {
    "id": 1,
    "quantity": 3
  }
}
```

### Удаление блюда из корзины

**Запрос:**
- Метод: `DELETE`
- Путь: `/basket/dish/{id}`
- Заголовок: `Authorization: Bearer {token}`

**Ответ:**
```json
{
  "status": "success",
  "message": "Блюдо удалено из корзины"
}
```

## Заказы (Orders)

### Создание заказа

**Запрос:**
- Метод: `POST`
- Путь: `/orders`
- Заголовок: `Authorization: Bearer {token}`
- Тело запроса:
  ```json
  {
    "id_address": 1,
    "comment": "Позвонить перед доставкой",
    "delivery_time": "2023-05-01 18:00:00"
  }
  ```

**Ответ:**
```json
{
  "status": "success",
  "message": "Заказ успешно создан",
  "data": {
    "order_id": 1
  }
}
```

### Обновление статуса заказа

**Запрос:**
- Метод: `PUT`
- Путь: `/orders/{id}`
- Заголовок: `Authorization: Bearer {token}`
- Тело запроса:
  ```json
  {
    "status": "completed"
  }
  ```

**Ответ:**
```json
{
  "status": "success",
  "message": "Статус заказа обновлен"
}
```

### Получение всех заказов (для администраторов)

**Запрос:**
- Метод: `GET`
- Путь: `/orders`
- Заголовок: `Authorization: Bearer {token}` (токен администратора)

**Ответ:**
```json
{
  "status": "success",
  "message": "Операция выполнена успешно",
  "data": [
    {
      "id_order": 1,
      "id_users": 2,
      "id_address": 1,
      "status": "processing",
      "comment": "Позвонить перед доставкой",
      "delivery_time": "2023-05-01 18:00:00",
      "created_at": "2023-05-01 12:30:00",
      "user": {
        "id_users": 2,
        "email": "user@example.com"
      },
      "address": "ул. Примерная, д. 1, кв. 1",
      "items": [
        {
          "id_order_item": 1,
          "id_order": 1,
          "id_dish": 5,
          "quantity": 2,
          "price": 500,
          "dish": {
            "id_dish": 5,
            "name": "Пицца Маргарита"
          }
        }
      ],
      "total": 1000
    }
  ]
}
```

### Получение заказов пользователя

**Запрос:**
- Метод: `GET`
- Путь: `/my-orders`
- Заголовок: `Authorization: Bearer {token}`

**Ответ:**
```json
{
  "status": "success",
  "message": "Операция выполнена успешно",
  "data": [
    {
      "id_order": 1,
      "id_users": 2,
      "id_address": 1,
      "status": "processing",
      "comment": "Позвонить перед доставкой",
      "delivery_time": "2023-05-01 18:00:00",
      "created_at": "2023-05-01 12:30:00",
      "address": "ул. Примерная, д. 1, кв. 1",
      "items": [
        {
          "id_order_item": 1,
          "id_order": 1,
          "id_dish": 5,
          "quantity": 2,
          "price": 500,
          "dish": {
            "id_dish": 5,
            "name": "Пицца Маргарита"
          }
        }
      ],
      "total": 1000
    }
  ]
}
```

## Бронирование (Reservation)

### Создание бронирования

**Запрос:**
- Метод: `POST`
- Путь: `/reservation`
- Тело запроса:
  ```json
  {
    "name": "Иван Иванов",
    "email": "ivan@example.com",
    "phone": "+7 (900) 123-45-67",
    "date": "2023-05-10",
    "time": "18:00",
    "guests": 4,
    "message": "Хотелось бы столик у окна"
  }
  ```

**Ответ:**
```json
{
  "status": "success",
  "message": "Бронирование успешно создано"
}
```

### Получение всех бронирований (для администраторов)

**Запрос:**
- Метод: `GET`
- Путь: `/reservation`
- Заголовок: `Authorization: Bearer {token}` (токен администратора)

**Ответ:**
```json
{
  "status": "success",
  "message": "Операция выполнена успешно",
  "data": [
    {
      "id_reservation": 1,
      "name": "Иван Иванов",
      "email": "ivan@example.com",
      "phone": "+7 (900) 123-45-67",
      "date": "2023-05-10",
      "time": "18:00",
      "guests": 4,
      "message": "Хотелось бы столик у окна",
      "created_at": "2023-05-01 12:00:00"
    }
  ]
}
```

## Категории (Category)

### Получение всех категорий

**Запрос:**
- Метод: `GET`
- Путь: `/category`

**Ответ:**
```json
{
  "status": "success",
  "message": "Операция выполнена успешно",
  "data": [
    {
      "id_category": 1,
      "name": "Супы",
      "description": "Горячие супы",
      "image": "uploads/images/soups.jpg"
    },
    {
      "id_category": 2,
      "name": "Пицца",
      "description": "Итальянская пицца",
      "image": "uploads/images/pizza.jpg"
    }
  ]
}
```

### Получение одной категории

**Запрос:**
- Метод: `GET`
- Путь: `/category/{id}`

**Ответ:**
```json
{
  "status": "success",
  "message": "Операция выполнена успешно",
  "data": {
    "id_category": 1,
    "name": "Супы",
    "description": "Горячие супы",
    "image": "uploads/images/soups.jpg",
    "dishes": [
      {
        "id_dish": 1,
        "name": "Борщ",
        "description": "Украинский борщ со сметаной",
        "price": 300,
        "image": "uploads/images/borsch.jpg",
        "id_category": 1
      }
    ]
  }
}
```

### Создание категории (для администраторов)

**Запрос:**
- Метод: `POST`
- Путь: `/category`
- Заголовок: `Authorization: Bearer {token}` (токен администратора)
- Формат запроса: `multipart/form-data`
- Поля:
  - `name` - название категории
  - `description` - описание категории
  - `image` - изображение категории (файл)

**Ответ:**
```json
{
  "status": "success",
  "message": "Категория успешно создана",
  "data": {
    "id": 3,
    "name": "Десерты",
    "description": "Сладкие десерты",
    "image": "uploads/images/desserts.jpg"
  }
}
```

### Обновление категории (для администраторов)

**Запрос:**
- Метод: `PUT`
- Путь: `/category/{id}`
- Заголовок: `Authorization: Bearer {token}` (токен администратора)
- Формат запроса: `multipart/form-data`
- Поля:
  - `name` - название категории (опционально)
  - `description` - описание категории (опционально)
  - `image` - изображение категории (файл, опционально)

**Ответ:**
```json
{
  "status": "success",
  "message": "Категория успешно обновлена",
  "data": {
    "id": 3,
    "name": "Десерты и торты",
    "description": "Сладкие десерты и торты",
    "image": "uploads/images/desserts_updated.jpg"
  }
}
```

### Удаление категории (для администраторов)

**Запрос:**
- Метод: `DELETE`
- Путь: `/category/{id}`
- Заголовок: `Authorization: Bearer {token}` (токен администратора)

**Ответ:**
```json
{
  "status": "success",
  "message": "Категория успешно удалена"
}
```

## Блюда (Dish/Product)

### Получение всех блюд

**Запрос:**
- Метод: `GET`
- Путь: `/product`
- Параметры запроса (опционально):
  - `category` - ID категории для фильтрации
  - `limit` - лимит количества блюд (по умолчанию 12)
  - `page` - номер страницы (по умолчанию 1)

**Ответ:**
```json
{
  "status": "success",
  "message": "Операция выполнена успешно",
  "data": {
    "dishes": [
      {
        "id_dish": 1,
        "name": "Борщ",
        "description": "Украинский борщ со сметаной",
        "price": 300,
        "image": "uploads/images/borsch.jpg",
        "id_category": 1,
        "category_name": "Супы"
      }
    ],
    "pagination": {
      "current_page": 1,
      "total_pages": 5,
      "total_items": 50,
      "limit": 12
    }
  }
}
```

### Получение одного блюда

**Запрос:**
- Метод: `GET`
- Путь: `/product/{id}`

**Ответ:**
```json
{
  "status": "success",
  "message": "Операция выполнена успешно",
  "data": {
    "id_dish": 1,
    "name": "Борщ",
    "description": "Украинский борщ со сметаной",
    "price": 300,
    "image": "uploads/images/borsch.jpg",
    "id_category": 1,
    "category_name": "Супы"
  }
}
```

### Создание блюда (для администраторов)

**Запрос:**
- Метод: `POST`
- Путь: `/product`
- Заголовок: `Authorization: Bearer {token}` (токен администратора)
- Формат запроса: `multipart/form-data`
- Поля:
  - `name` - название блюда
  - `description` - описание блюда
  - `price` - цена блюда (в рублях)
  - `id_category` - ID категории
  - `image` - изображение блюда (файл)

**Ответ:**
```json
{
  "status": "success",
  "message": "Блюдо успешно создано",
  "data": {
    "id": 6,
    "name": "Салат Цезарь",
    "description": "Классический салат Цезарь с курицей",
    "price": 450,
    "image": "uploads/images/caesar.jpg",
    "id_category": 3
  }
}
```

### Обновление блюда (для администраторов)

**Запрос:**
- Метод: `POST`
- Путь: `/product/{id}`
- Заголовок: `Authorization: Bearer {token}` (токен администратора)
- Формат запроса: `multipart/form-data`
- Поля:
  - `name` - название блюда (опционально)
  - `description` - описание блюда (опционально)
  - `price` - цена блюда (опционально)
  - `id_category` - ID категории (опционально)
  - `image` - изображение блюда (файл, опционально)

**Ответ:**
```json
{
  "status": "success",
  "message": "Блюдо успешно обновлено",
  "data": {
    "id": 6,
    "name": "Салат Цезарь с креветками",
    "description": "Классический салат Цезарь с креветками",
    "price": 550,
    "image": "uploads/images/caesar_updated.jpg",
    "id_category": 3
  }
}
```

### Удаление блюда (для администраторов)

**Запрос:**
- Метод: `DELETE`
- Путь: `/product/{id}`
- Заголовок: `Authorization: Bearer {token}` (токен администратора)

**Ответ:**
```json
{
  "status": "success",
  "message": "Блюдо успешно удалено"
}
```

## Блог (Blog)

### Получение всех статей блога

**Запрос:**
- Метод: `GET`
- Путь: `/blog`
- Параметры запроса (опционально):
  - `limit` - лимит количества статей (по умолчанию 10)
  - `page` - номер страницы (по умолчанию 1)

**Ответ:**
```json
{
  "status": "success",
  "message": "Операция выполнена успешно",
  "data": {
    "posts": [
      {
        "id_blog": 1,
        "title": "История пиццы",
        "content": "Пицца имеет древние корни...",
        "image": "uploads/images/pizza_history.jpg",
        "created_at": "2023-05-01 12:00:00",
        "updated_at": "2023-05-01 12:00:00"
      }
    ],
    "pagination": {
      "current_page": 1,
      "total_pages": 3,
      "total_items": 25,
      "limit": 10
    }
  }
}
```

### Получение одной статьи блога

**Запрос:**
- Метод: `GET`
- Путь: `/blog/{id}`

**Ответ:**
```json
{
  "status": "success",
  "message": "Операция выполнена успешно",
  "data": {
    "id_blog": 1,
    "title": "История пиццы",
    "content": "Пицца имеет древние корни...",
    "image": "uploads/images/pizza_history.jpg",
    "created_at": "2023-05-01 12:00:00",
    "updated_at": "2023-05-01 12:00:00"
  }
}
```

### Создание статьи блога (для администраторов)

**Запрос:**
- Метод: `POST`
- Путь: `/blog`
- Заголовок: `Authorization: Bearer {token}` (токен администратора)
- Формат запроса: `multipart/form-data`
- Поля:
  - `title` - заголовок статьи
  - `content` - содержимое статьи
  - `image` - изображение для статьи (файл)

**Ответ:**
```json
{
  "status": "success",
  "message": "Статья успешно создана",
  "data": {
    "id": 2,
    "title": "Рецепты итальянских соусов",
    "content": "В этой статье мы рассмотрим...",
    "image": "uploads/images/sauces.jpg"
  }
}
```

### Обновление статьи блога (для администраторов)

**Запрос:**
- Метод: `POST`
- Путь: `/blog/{id}`
- Заголовок: `Authorization: Bearer {token}` (токен администратора)
- Формат запроса: `multipart/form-data`
- Поля:
  - `title` - заголовок статьи (опционально)
  - `content` - содержимое статьи (опционально)
  - `image` - изображение для статьи (файл, опционально)

**Ответ:**
```json
{
  "status": "success",
  "message": "Статья успешно обновлена",
  "data": {
    "id": 2,
    "title": "Рецепты итальянских соусов от шефа",
    "content": "В этой обновленной статье мы рассмотрим...",
    "image": "uploads/images/sauces_updated.jpg"
  }
}
```

### Удаление статьи блога (для администраторов)

**Запрос:**
- Метод: `DELETE`
- Путь: `/blog/{id}`
- Заголовок: `Authorization: Bearer {token}` (токен администратора)

**Ответ:**
```json
{
  "status": "success",
  "message": "Статья успешно удалена"
}
```

## Коды состояния HTTP

- `200 OK` - Запрос выполнен успешно
- `201 Created` - Ресурс успешно создан
- `400 Bad Request` - Ошибка в запросе (неправильный формат данных, отсутствие обязательных полей)
- `401 Unauthorized` - Требуется аутентификация или предоставленный токен недействителен
- `403 Forbidden` - Недостаточно прав для выполнения операции
- `404 Not Found` - Запрашиваемый ресурс не найден
- `409 Conflict` - Конфликт (например, пользователь с таким email уже существует)
- `500 Internal Server Error` - Внутренняя ошибка сервера 