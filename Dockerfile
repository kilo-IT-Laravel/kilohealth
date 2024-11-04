# Use the official PHP 8.2 image with Apache
FROM php:8.2-apache

# Set the working directory in the container
WORKDIR /var/www

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    zip \
    unzip \
    libonig-dev \
    libxml2-dev \
    npm

# Install PHP extensions
RUN docker-php-ext-install pdo pdo_mysql mbstring exif pcntl bcmath gd

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Enable Apache mod_rewrite
RUN a2enmod rewrite

# Copy existing application directory contents to the container
COPY . /var/www

# Change ownership of Laravel folder
RUN chown -R www-data:www-data /var/www \
    && chmod -R 755 /var/www/storage \
    && chmod -R 755 /var/www/bootstrap/cache

# Install Laravel dependencies
RUN composer install --optimize-autoloader --no-dev

RUN cp .env.example .env
RUN php artisan key:generate

# Install npm dependencies for Vue.js
RUN npm install && npm run build

RUN cp ./000-default.conf /etc/apache2/sites-available/000-default.conf

# Expose port 80
EXPOSE 80

# Start Apache in foreground mode (so it doesn't exit)
CMD ["apache2-foreground"]