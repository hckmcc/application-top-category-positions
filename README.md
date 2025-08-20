# Application Top Category Positions
## Описание
* Проект реализует модуль Application Top Category Positions для получения данных о позициях приложения в топе по категориям за определенный день.
## Технологии
* PHP 8.2
* Laravel 12.25.0
* PostgreSQL 17.6
* NGINX
## Что было реализовано
### Сборка окружения
* Dockerfile — Конфигурация для создания контейнеров Docker.
* docker-compose.yml — Настройки для запуска контейнеров с помощью Docker Compose.
* .env — Файл с переменными окружения для настройки конфигураций.
* migrations — Миграции для создания таблиц для хранения prepared данных для endpoint-а и логов запросов на endpoint.
* api.php — Определения маршрутов для API.
* README.md — Описание проекта, инструкция по установке и запуску.
### Реализация логики
* Controllers - Контроллер для обработки запросов пользователей.
* Requests - Для валидации данных.
* Models - Для взаимодействия с БД.
* Services - Для инкапсуляции логики, необходимой для выполнения определенных задач.

## Запуск приложения
1. Для запуска приложения необходимы Docker и Docker Compose.
2. Клонируйте репозиторий:
```bash
git clone git@github.com:hckmcc/application-top-category-positions.git
```
3.  Скопируйте содержимое `.env.example` в `.env`
4. Запустите сервер:
```bash
docker compose build
docker compose up -d
```
5. Перейдите в контейнер php-fpm:

```bash
docker compose exec php-fpm bash
 ```

6. Из контейнера php-fpm запустите миграции:

```bash
php artisan migrate
```

7. Из контейнера php-fpm запустите команду для заполнения prepared данных:

Загрузка данных за последние 30 дней:
```bash
php artisan apptica:fetch-positions
```
Загрузка данных за конкретный день:
```bash
php artisan apptica:fetch-positions 2025-08-05
```

8. После запуска сервера можно работать с API через такие сервисы как Postman.

## Эндпоинты
### 1. Получение информации с позициями приложения в топе по категориям для запрашиваемой даты
- **GET**  `http://localhost:8082/api/appTopCategory?date=2025-08-05`
- Параметры:
    - date (обязательно) - запрашиваемая дата

  **Ответ:**
- **Статус:** `200 OK`
```json
   {
    "status_code": 200,
    "message": "ok",
    "data": {
        "2": 47,
        "23": 4,
        "134": 182
    }
}
```
**Коды состояния**:
- `200 OK`: Данные успешно получены.
- `404 Not found`: Для указанной даты не найдено данных.
- `422 Unprocessable Content`: Ошибка валидации данных.
