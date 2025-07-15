# Use official PHP image with Apache
FROM php:8.2-apache

# Install dependencies
RUN apt-get update && apt-get install -y \
    git zip unzip libzip-dev libpng-dev libonig-dev libxml2-dev curl autoconf g++ make \
    build-essential libssl-dev \
    && docker-php-ext-install pdo pdo_mysql zip mbstring gd

# Install and enable gRPC extension
RUN pecl install grpc && docker-php-ext-enable grpc

# Enable sodium
RUN docker-php-ext-install sodium || true

# Enable Apache mod_rewrite
RUN a2enmod rewrite

# Set working directory
WORKDIR /var/www/html

# Copy Laravel project files
COPY . /var/www/html

# Copy custom Apache configuration
COPY apache-conf.conf /etc/apache2/sites-available/apache-conf.conf

# Enable custom Apache configuration and disable default
RUN a2dissite 000-default.conf
RUN a2ensite apache-conf.conf


# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Install Laravel dependencies
RUN composer install --optimize-autoloader --no-dev

# Set permissions
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Copy .env.production later in Render

# Expose port 80
EXPOSE 80
