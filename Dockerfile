FROM php:8.2-cli

# Instalar extensões necessárias
RUN docker-php-ext-install pdo pdo_mysql

WORKDIR /var/www/html

CMD ["php", "-S", "0.0.0.0:8000", "-t", "/var/www/html"] 