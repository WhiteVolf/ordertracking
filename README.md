# ordertracking
Order Tracking System

Цей проєкт реалізує API для управління замовленнями, розроблений на Laravel 11. API підтримує повний CRUD для замовлень, а також додаткові функції, такі як авторизація з JWT, експорт у CSV/Excel, сповіщення через Reverb, черги Redis та моніторинг за допомогою Laravel Horizon.

## Функціональні можливості

- **Аутентифікація**: Авторизація користувача з використанням JWT.
- **CRUD для замовлень**: Користувачі можуть створювати, читати, оновлювати та видаляти власні замовлення.
- **Обмеження доступу**: Користувачі бачать лише свої замовлення.
- **Сповіщення**: Відправка сповіщень про створення та зміну статусу замовлення через Reverb.
- **Експорт замовлень**: Можливість експорту у формат CSV та Excel.
- **Підтримка черг**: Laravel Horizon для моніторингу черг Redis.
- **Пагінація та фільтрація**: Підтримка пагінації та фільтрації за статусом у списку замовлень.

## Встановлення

1. **Клонування репозиторію**:
   ```bash
   git clone git@github.com:WhiteVolf/ordertracking.git
   cd ordertracking

Встановлення залежностей:

composer install

Конфігурація середовища: Створіть .env файл, скопіювавши .env.example, та налаштуйте такі параметри:

env
APP_KEY= # згенеруйте новий ключ
DB_DATABASE= # назва бази даних
DB_USERNAME= # користувач бази даних
DB_PASSWORD= # пароль бази даних
JWT_SECRET= # згенеруйте новий секрет для JWT
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

Генеруйте новий APP_KEY:

php artisan key:generate

Міграції та сидери: Створіть таблиці бази даних та додайте необхідні сидери:

php artisan migrate --seed

Laravel Horizon: Запустіть Horizon для моніторингу черг:

php artisan horizon

Запуск проєкту

Для локального запуску сервера виконайте:

php artisan serve

API Роутинг
POST /api/auth/register – Реєстрація нового користувача
POST /api/auth/login – Авторизація користувача та отримання JWT токена
GET /api/orders – Отримати список замовлень (з пагінацією та фільтрацією)
POST /api/orders – Створити нове замовлення
GET /api/orders/{id} – Отримати конкретне замовлення
PUT /api/orders/{id} – Оновити замовлення
DELETE /api/orders/{id} – Видалити замовлення
GET /api/orders/export/{format} – Експорт замовлень у форматі CSV або Excel (format = csv або excel)

Тестування

Для запуску юніт тестів виконайте:

php artisan test
Тести охоплюють всі ключові функції, включаючи авторизацію, CRUD-операції для замовлень, експорт і сповіщення.

Horizon
Laravel Horizon забезпечує моніторинг черг. Ви можете переглянути статус черг за адресою:

http://localhost/horizon
Експорт Замовлень
Для експорту замовлень API підтримує формат CSV та Excel. Просто виконайте запит на /api/orders/export/csv або /api/orders/export/excel.

Сповіщення
Система використовує Reverb для сповіщень. Сповіщення надсилаються при створенні замовлення або зміні його статусу.

Ліцензія
Цей проєкт ліцензований під ліцензією MIT.