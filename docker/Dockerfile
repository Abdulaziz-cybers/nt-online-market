FROM php:8.3-fpm

WORKDIR /var/www

# Установка зависимостей и расширений PHP
RUN apt-get update && apt-get install -y --no-install-recommends \
    git \
    curl \
    zip \
    unzip \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libzip-dev \
    libicu-dev \
    libxslt-dev \
    libpq-dev \
    libonig-dev \
    ca-certificates \
    gnupg \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-configure intl \
    && docker-php-ext-install -j$(nproc) \
        gd \
        zip \
        pdo \
        pdo_mysql \
        pdo_pgsql \
        intl \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Установка Node.js 20 LTS напрямую
RUN curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
    && apt-get install -y nodejs \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Установка Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Копируем все файлы проекта
COPY . /var/www

# Установка зависимостей Laravel
RUN composer install --no-interaction --prefer-dist --optimize-autoloader

# Laravel Key (если .env уже есть)
RUN php artisan key:generate || true

# Установка npm-зависимостей (включая devDependencies)
RUN npm install

# Сборка ассетов (vite или другой инструмент)
RUN npm run build

# (опционально) Очистка dev-зависимостей
RUN npm prune --omit=dev

EXPOSE 9000

CMD ["php-fpm"]
