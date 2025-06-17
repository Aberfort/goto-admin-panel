# Використовуємо PHP 8.2
FROM php:8.2-fpm

# Встановлюємо системні залежності та PHP-розширення
RUN apt-get update && apt-get install -y \
    build-essential \
    libpng-dev \
    libjpeg62-turbo-dev \
    libfreetype6-dev \
    libonig-dev \
    libzip-dev \
    libxml2-dev \
    zip \
    unzip \
    git \
    curl \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Встановлюємо Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Встановлюємо робочу директорію
WORKDIR /var/www/html

# Копіюємо весь проєкт
COPY . /var/www/html

# Встановлюємо залежності Laravel
RUN composer install --no-interaction --prefer-dist --optimize-autoloader

# Надаємо права на директорії storage та bootstrap/cache
RUN chown -R www-data:www-data storage bootstrap/cache

# Експонуємо порт
EXPOSE 9000

# Запускаємо PHP-FPM
CMD ["php-fpm"]

