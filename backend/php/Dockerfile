FROM php:8.3-fpm

# 1. Системные зависимости
RUN apt-get update && apt-get install -y \
    libpq-dev \
    libpng-dev \
    libjpeg62-turbo-dev \
    libwebp-dev \
    libfreetype6-dev \
    libzip-dev \
    libicu-dev \
    zip \
    unzip \
    git \
    curl \
    gnupg2 \
    ca-certificates \
    supervisor \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# 2. Node.js 20 (если нужен Vite/npm)
RUN curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
    && apt-get install -y nodejs \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# 3. PHP-расширения
RUN docker-php-ext-configure gd --with-jpeg --with-webp --with-freetype \
    && docker-php-ext-install pdo_pgsql pgsql gd zip intl bcmath opcache

# 4. Redis через git (надёжнее pecl)
RUN git clone https://github.com/phpredis/phpredis.git /tmp/phpredis \
    && cd /tmp/phpredis \
    && phpize \
    && ./configure \
    && make \
    && make install \
    && docker-php-ext-enable redis \
    && rm -rf /tmp/phpredis

# 5. Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# 6. Рабочая директория и подготовка структуры Laravel
WORKDIR /var/www/html

RUN mkdir -p /var/www/html/storage /var/www/html/bootstrap/cache \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# 7. PHP-FPM слушает на всех интерфейсах
RUN echo "listen = 0.0.0.0:9000" >> /usr/local/etc/php-fpm.d/www.conf

COPY backend/php/conf.d/99-docker-mount-performance.ini /usr/local/etc/php/conf.d/99-docker-mount-performance.ini

COPY backend/php/docker-entrypoint.sh /usr/local/bin/demo-entrypoint.sh
RUN chmod +x /usr/local/bin/demo-entrypoint.sh

EXPOSE 9000 5173

ENTRYPOINT ["demo-entrypoint.sh"]