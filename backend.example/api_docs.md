# Документация по API медицинского центра

API для медицинского центра. Все запросы к API выполняются по URL `/api/{entity}/{method}` с использованием соответствующих HTTP методов.

## Аутентификация

Большинство запросов требуют аутентификации с использованием JWT токена. Токен передается в заголовке `Authorization` в формате `Bearer {token}`.

### Структура ответа

Все ответы API возвращаются в формате JSON и имеют следующую структуру:

```json
{
    "status": "success", // или "error"
    "message": "Сообщение о результате операции",
    "data": {} // Данные, возвращаемые API
}
```

## Пользователи (Users)

### Регистрация

- URL: `/api/users/register`
- Метод: `POST`
- Требует аутентификации: Нет
- Тело запроса:
```json
{
    "FIO": "Иванов Иван Иванович",
    "phone": "+79001234567",
    "email": "user@example.com",
    "password": "password123"
}
```
- Ответ:
```json
{
    "status": "success",
    "message": "Регистрация успешно завершена",
    "data": {
        "token": "JWT_TOKEN"
    }
}
```

### Авторизация

- URL: `/api/users/login`
- Метод: `POST`
- Требует аутентификации: Нет
- Тело запроса:
```json
{
    "email": "user@example.com",
    "password": "password123"
}
```
- Ответ:
```json
{
    "status": "success",
    "message": "Авторизация успешна",
    "data": {
        "token": "JWT_TOKEN",
        "user": {
            "id_users": 1,
            "FIO": "Иванов Иван Иванович",
            "phone": "+79001234567",
            "email": "user@example.com",
            "op": 0
        }
    }
}
```

### Выход

- URL: `/api/users/logout`
- Метод: `GET`
- Требует аутентификации: Нет (JWT токен должен быть удален на стороне клиента)
- Ответ:
```json
{
    "status": "success",
    "message": "Выход выполнен успешно"
}
```

### Получение профиля

- URL: `/api/users/profile`
- Метод: `GET`
- Требует аутентификации: Да
- Ответ:
```json
{
    "status": "success",
    "message": "Профиль получен успешно",
    "data": {
        "id_users": 1,
        "FIO": "Иванов Иван Иванович",
        "phone": "+79001234567",
        "email": "user@example.com",
        "op": 0
    }
}
```

## Отзывы (Reviews)

### Получение всех отзывов

- URL: `/api/reviews/index`
- Метод: `GET`
- Требует аутентификации: Нет
- Ответ:
```json
{
    "status": "success",
    "message": "Список отзывов получен успешно",
    "data": [
        {
            "id_reviews": 1,
            "phone": "+79001234567",
            "text_positive": "Отличный врач, рекомендую!",
            "text_negative": null,
            "id_expert": 1,
            "expert_name": "Петров Петр Петрович"
        },
        // Другие отзывы
    ]
}
```

### Получение отзывов по ID эксперта

- URL: `/api/reviews/showByExpert/{id_expert}`
- Метод: `GET`
- Требует аутентификации: Нет
- Ответ:
```json
{
    "status": "success",
    "message": "Отзывы по эксперту получены успешно",
    "data": [
        {
            "id_reviews": 1,
            "phone": "+79001234567",
            "text_positive": "Отличный врач, рекомендую!",
            "text_negative": null,
            "id_expert": 1,
            "expert_name": "Петров Петр Петрович"
        },
        // Другие отзывы этого эксперта
    ]
}
```

### Добавление отзыва

- URL: `/api/reviews/store`
- Метод: `POST`
- Требует аутентификации: Нет
- Тело запроса:
```json
{
    "phone": "+79001234567",
    "text_positive": "Отличный врач, рекомендую!",
    "text_negative": "Немного задержался прием",
    "id_expert": 1
}
```
- Ответ:
```json
{
    "status": "success",
    "message": "Отзыв успешно добавлен",
    "data": {
        "id_reviews": 1
    }
}
```

## Заявки (Application)

### Получение списка заявок

- URL: `/api/application/index`
- Метод: `GET`
- Требует аутентификации: Нет
- Ответ:
```json
{
    "status": "success",
    "message": "Список заявок получен успешно",
    "data": [
        {
            "id_application": 1,
            "user_name": "Иванов Иван",
            "phone": "+79001234567"
        },
        // Другие заявки
    ]
}
```

### Получение заявки по ID

- URL: `/api/application/show/{id}`
- Метод: `GET`
- Требует аутентификации: Нет
- Ответ:
```json
{
    "status": "success",
    "message": "Заявка получена успешно",
    "data": {
        "id_application": 1,
        "user_name": "Иванов Иван",
        "phone": "+79001234567"
    }
}
```

### Добавление заявки

- URL: `/api/application/store`
- Метод: `POST`
- Требует аутентификации: Нет
- Тело запроса:
```json
{
    "user_name": "Иванов Иван",
    "phone": "+79001234567"
}
```
- Ответ:
```json
{
    "status": "success",
    "message": "Заявка успешно добавлена",
    "data": {
        "id_application": 1
    }
}
```

## Записи (Records)

### Получение списка записей

- URL: `/api/records/index`
- Метод: `GET`
- Требует аутентификации: Да (администратор)
- Ответ:
```json
{
    "status": "success",
    "message": "Список записей получен успешно",
    "data": [
        {
            "id_records": 1,
            "fio_user": "Иванов Иван Иванович",
            "phone_user": "+79001234567",
            "expert": "Петров Петр Петрович",
            "date": "2023-10-15",
            "time": "14:30:00"
        },
        // Другие записи
    ]
}
```

### Получение записи по ID

- URL: `/api/records/show/{id}`
- Метод: `GET`
- Требует аутентификации: Да (администратор)
- Ответ:
```json
{
    "status": "success",
    "message": "Запись получена успешно",
    "data": {
        "id_records": 1,
        "fio_user": "Иванов Иван Иванович",
        "phone_user": "+79001234567",
        "expert": "Петров Петр Петрович",
        "date": "2023-10-15",
        "time": "14:30:00"
    }
}
```

### Добавление записи

- URL: `/api/records/store`
- Метод: `POST`
- Требует аутентификации: Да (администратор)
- Тело запроса:
```json
{
    "fio_user": "Иванов Иван Иванович",
    "phone_user": "+79001234567",
    "expert": "Петров Петр Петрович",
    "date": "2023-10-15",
    "time": "14:30:00"
}
```
- Ответ:
```json
{
    "status": "success",
    "message": "Запись успешно создана",
    "data": {
        "id": 1
    }
}
```

### Обновление записи

- URL: `/api/records/update/{id}`
- Метод: `PUT`
- Требует аутентификации: Да (администратор)
- Тело запроса:
```json
{
    "fio_user": "Иванов Иван Иванович",
    "phone_user": "+79001234567",
    "expert": "Петров Петр Петрович",
    "date": "2023-10-16",
    "time": "15:00:00"
}
```
- Ответ:
```json
{
    "status": "success",
    "message": "Запись успешно обновлена"
}
```

### Удаление записи

- URL: `/api/records/delete/{id}`
- Метод: `DELETE`
- Требует аутентификации: Да (администратор)
- Ответ:
```json
{
    "status": "success",
    "message": "Запись успешно удалена"
}
```

## Категории (Category)

### Получение списка категорий

- URL: `/api/category/index`
- Метод: `GET`
- Требует аутентификации: Нет
- Ответ:
```json
{
    "status": "success",
    "message": "Список категорий получен успешно",
    "data": [
        {
            "id_category": 1,
            "category_name": "Терапия",
            "description": "Лечение внутренних болезней",
            "photo": "categories/therapy.jpg"
        },
        // Другие категории
    ]
}
```

### Получение категории по ID

- URL: `/api/category/show/{id}`
- Метод: `GET`
- Требует аутентификации: Нет
- Ответ:
```json
{
    "status": "success",
    "message": "Категория получена успешно",
    "data": {
        "id_category": 1,
        "category_name": "Терапия",
        "description": "Лечение внутренних болезней",
        "photo": "categories/therapy.jpg"
    }
}
```

### Добавление категории

- URL: `/api/category/store`
- Метод: `POST`
- Требует аутентификации: Да (администратор)
- Тело запроса: multipart/form-data
  - category_name: "Терапия"
  - description: "Лечение внутренних болезней"
  - photo: [файл изображения]
- Ответ:
```json
{
    "status": "success",
    "message": "Категория успешно добавлена",
    "data": {
        "id_category": 1
    }
}
```

### Обновление категории

- URL: `/api/category/update/{id}`
- Метод: `POST` (с полем _method=PUT) или `PUT`
- Требует аутентификации: Да (администратор)
- Тело запроса: multipart/form-data
  - category_name: "Терапия"
  - description: "Обновленное описание"
  - photo: [файл изображения] (необязательно)
- Ответ:
```json
{
    "status": "success",
    "message": "Категория успешно обновлена"
}
```

### Удаление категории

- URL: `/api/category/delete/{id}`
- Метод: `POST` (с полем _method=DELETE) или `DELETE`
- Требует аутентификации: Да (администратор)
- Ответ:
```json
{
    "status": "success",
    "message": "Категория успешно удалена"
}
```

## Эксперты (Experts)

### Получение списка экспертов

- URL: `/api/experts/index`
- Метод: `GET`
- Требует аутентификации: Нет
- Ответ:
```json
{
    "status": "success",
    "message": "Список экспертов получен успешно",
    "data": [
        {
            "id_expert": 1,
            "FIO": "Петров Петр Петрович",
            "category_name": "Терапия",
            "photo": "experts/petrov.jpg",
            "expert_discription": "Опытный врач-терапевт",
            "expert_education": "Медицинский университет им. Павлова",
            "expert_experience": "10 лет практики"
        },
        // Другие эксперты
    ]
}
```

### Получение эксперта по ID

- URL: `/api/experts/show/{id}`
- Метод: `GET`
- Требует аутентификации: Нет
- Ответ:
```json
{
    "status": "success",
    "message": "Эксперт получен успешно",
    "data": {
        "id_expert": 1,
        "FIO": "Петров Петр Петрович",
        "category_name": "Терапия",
        "photo": "experts/petrov.jpg",
        "expert_discription": "Опытный врач-терапевт",
        "expert_education": "Медицинский университет им. Павлова",
        "expert_experience": "10 лет практики"
    }
}
```

### Добавление эксперта

- URL: `/api/experts/store`
- Метод: `POST`
- Требует аутентификации: Да (администратор)
- Тело запроса: multipart/form-data
  - FIO: "Петров Петр Петрович"
  - category_name: "Терапия"
  - expert_discription: "Опытный врач-терапевт"
  - expert_education: "Медицинский университет им. Павлова"
  - expert_experience: "10 лет практики"
  - photo: [файл изображения]
- Ответ:
```json
{
    "status": "success",
    "message": "Эксперт успешно добавлен",
    "data": {
        "id": 1
    }
}
```

### Обновление эксперта

- URL: `/api/experts/update/{id}`
- Метод: `POST` (с полем _method=PUT) или `PUT`
- Требует аутентификации: Да (администратор)
- Тело запроса: multipart/form-data
  - FIO: "Петров Петр Петрович"
  - category_name: "Терапия"
  - expert_discription: "Обновленное описание"
  - expert_education: "Обновленное образование"
  - expert_experience: "12 лет практики"
  - photo: [файл изображения] (необязательно)
- Ответ:
```json
{
    "status": "success",
    "message": "Эксперт успешно обновлен"
}
```

### Удаление эксперта

- URL: `/api/experts/delete/{id}`
- Метод: `POST` (с полем _method=DELETE) или `DELETE`
- Требует аутентификации: Да (администратор)
- Ответ:
```json
{
    "status": "success",
    "message": "Эксперт успешно удален"
}
```

## Услуги (Services)

### Получение списка услуг

- URL: `/api/services/index`
- Метод: `GET`
- Требует аутентификации: Нет
- Ответ:
```json
{
    "status": "success",
    "message": "Список услуг получен успешно",
    "data": [
        {
            "id_services": 1,
            "category_name": "Терапия",
            "services_name": "Первичный прием",
            "price": 2000.00
        },
        // Другие услуги
    ]
}
```

### Получение услуги по ID

- URL: `/api/services/show/{id}`
- Метод: `GET`
- Требует аутентификации: Нет
- Ответ:
```json
{
    "status": "success",
    "message": "Услуга получена успешно",
    "data": {
        "id_services": 1,
        "category_name": "Терапия",
        "services_name": "Первичный прием",
        "price": 2000.00
    }
}
```

### Получение услуг по категории

- URL: `/api/services/getByCategory/{category_name}`
- Метод: `GET`
- Требует аутентификации: Нет
- Ответ:
```json
{
    "status": "success",
    "message": "Список услуг по категории получен успешно",
    "data": [
        {
            "id_services": 1,
            "category_name": "Терапия",
            "services_name": "Первичный прием",
            "price": 2000.00
        },
        {
            "id_services": 2,
            "category_name": "Терапия",
            "services_name": "Повторный прием",
            "price": 1500.00
        },
        // Другие услуги в этой категории
    ]
}
```

### Добавление услуги

- URL: `/api/services/store`
- Метод: `POST`
- Требует аутентификации: Да (администратор)
- Тело запроса:
```json
{
    "category_name": "Терапия",
    "services_name": "Первичный прием",
    "price": 2000.00
}
```
- Ответ:
```json
{
    "status": "success",
    "message": "Услуга успешно создана",
    "data": {
        "id": 1
    }
}
```

### Обновление услуги

- URL: `/api/services/update/{id}`
- Метод: `PUT`
- Требует аутентификации: Да (администратор)
- Тело запроса:
```json
{
    "category_name": "Терапия",
    "services_name": "Первичный прием врача-терапевта",
    "price": 2200.00
}
```
- Ответ:
```json
{
    "status": "success",
    "message": "Услуга успешно обновлена"
}
```

### Удаление услуги

- URL: `/api/services/delete/{id}`
- Метод: `DELETE`
- Требует аутентификации: Да (администратор)
- Ответ:
```json
{
    "status": "success",
    "message": "Услуга успешно удалена"
}
``` 