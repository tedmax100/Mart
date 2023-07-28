FROM php:8.1-fpm

# 安裝所需的系統套件和 PHP 擴充功能
RUN apt-get update && apt-get install -y \
    curl \
    unzip \
    libzip-dev \
    git \
    gcc \
    make \
    autoconf \
    && docker-php-ext-install mysqli pdo pdo_mysql zip

RUN pecl install opentelemetry-beta && docker-php-ext-enable opentelemetry
# 設定工作目錄
WORKDIR /var/www/html

EXPOSE 8000
CMD ["php", "artisan", "serve", "--host", "0.0.0.0", "--port", "8000"]
