
# Backend - Laravel

Цей проект є бекенд частиною додатку, реалізованою на фреймворку Laravel. Він надає RESTful API для керування вебсайтами та обробки автентифікації користувачів через Laravel Sanctum.




## Особливості
**Авторизація та Аутентифікація:** Реєстрація, вхід та вихід користувачів з використанням Laravel Sanctum.

**CRUD Операції для Вебсайтів:** Створення, перегляд, оновлення та видалення вебсайтів.

**Генерація API Токенів:** Можливість перегенерувати API токен для кожного вебсайту.

**Захищені Маршрути:** Маршрути, що вимагають аутентифікації через Sanctum.
## Вимоги
**PHP** >= 8.0

**Composer**

**MySQL** або інша підтримувана база даних

**Git** Маршрути, що вимагають аутентифікації через Sanctum.

Рекомендовано використовувати **Docker** для швидкого розгортання, але не обов'язково.
## Установка та Налаштування

### Клонування Репозиторію

```bash
git clone git@git.sharkscode.com:serhiiv/goto-admin-panel.git
cd goto-admin-panel/backend
```

### Встановлення Залежностей

```bash
composer install
```

### Налаштування Файлу .env

Створіть копію з .env.example

```bash
cp .env.example .env
```

Відкрийте .env та налаштуйте:

```bash
APP_NAME=Laravel
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://localhost:8080

LOG_CHANNEL=stack
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=debug

DB_CONNECTION=mysql
DB_HOST=db
DB_PORT=3306
DB_DATABASE=your_database_name
DB_USERNAME=your_database_user
DB_PASSWORD=your_database_password

BROADCAST_DRIVER=log
CACHE_DRIVER=file
FILESYSTEM_DISK=local
QUEUE_CONNECTION=sync
SESSION_DRIVER=file
SESSION_LIFETIME=120
SANCTUM_STATEFUL_DOMAINS=localhost:8080,localhost:3000
SESSION_DOMAIN=localhost
SESSION_SECURE_COOKIE=true

MEMCACHED_HOST=redis

REDIS_HOST=redis
REDIS_PASSWORD=null
REDIS_PORT=6379

MAIL_MAILER=smtp
MAIL_HOST=mailpit
MAIL_PORT=1025
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS="hello@example.com"
MAIL_FROM_NAME="${APP_NAME}"

AWS_ACCESS_KEY_ID=
AWS_SECRET_ACCESS_KEY=
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=
AWS_USE_PATH_STYLE_ENDPOINT=false

PUSHER_APP_ID=
PUSHER_APP_KEY=
PUSHER_APP_SECRET=
PUSHER_HOST=
PUSHER_PORT=443
PUSHER_SCHEME=https
PUSHER_APP_CLUSTER=mt1

VITE_APP_NAME="${APP_NAME}"
VITE_PUSHER_APP_KEY="${PUSHER_APP_KEY}"
VITE_PUSHER_HOST="${PUSHER_HOST}"
VITE_PUSHER_PORT="${PUSHER_PORT}"
VITE_PUSHER_SCHEME="${PUSHER_SCHEME}"
VITE_PUSHER_APP_CLUSTER="${PUSHER_APP_CLUSTER}"
```

### Генерація Ключа Додатку

```bash
php artisan key:generate
```

### Міграції

```bash
php artisan migrate
```

### Запуск Проекту

```bash
php artisan serve
```

Проект буде доступний за адресою http://localhost:8080

## Структура Проекту

**app/:** Логіка додатку (Моделі, Контролери, Сервіси).

**config/:** Конфігураційні файли.

**database/:** Міграції, сидери, фабрики.

**routes/:** Маршрути web.php та api.php.

**resources/:** В’юхи (якщо є), Blade-шаблони.

**tests/:** Тести.

**public/:** Точка входу (index.php).
## Робота з API

### Перевірка API:

Реєстрація: POST /api/register

Вхід: POST /api/login

Вихід: POST /api/logout

Поточний Користувач: GET /api/user

### CRUD для Вебсайтів (автентифікація потрібна):

Список: GET /api/websites

Створення: POST /api/websites

Перегляд: GET /api/websites/{id}

Оновлення: PUT /api/websites/{id}

Видалення: DELETE /api/websites/{id}

Перегенерація Токену: POST /api/websites/{id}/regenerate-token
