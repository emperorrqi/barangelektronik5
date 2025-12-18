FROM php:8.2-apache

# Install mysqli & PDO MySQL
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Enable Apache mod_rewrite
RUN a2enmod rewrite

# Copy aplikasi
COPY ./app /var/www/html

# Give permission
RUN chown -R www-data:www-data /var/www/html
