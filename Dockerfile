# Use official PHP image with Apache
FROM php:8.2-apache

# Install dependencies
RUN apt-get update && apt-get install -y \
    git zip unzip libzip-dev libpng-dev libonig-dev libxml2-dev curl \
    && docker-php-ext-install pdo pdo_mysql zip mbstring gd

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

# Set permissions
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Install Laravel dependencies
RUN composer install --optimize-autoloader --no-dev

# Copy .env.production later in Render

# Expose port 80
EXPOSE 80
