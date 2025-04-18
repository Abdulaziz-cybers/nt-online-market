FROM php:8.3-fpm

WORKDIR /var/www

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
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-configure intl \
    && docker-php-ext-install \
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

RUN curl -fsSL https://deb.nodesource.com/setup_16.x | bash - \
    && apt-get install -y nodejs \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

COPY . /var/www
RUN composer install
RUN php artisan key:generate

# Install npm dependencies (e.g., production dependencies)
RUN npm install --production

# Build frontend assets (if you're using something like Vite, Webpack, etc.)
RUN npm run build

# Expose port (if needed)
EXPOSE 9000

# Command to run the PHP-FPM server
CMD ["php-fpm"]
