FROM php:8.4-cli

WORKDIR /app

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    sqlite3 \
    libsqlite3-dev

# Install PHP extensions
RUN docker-php-ext-install pdo pdo_sqlite mbstring exif pcntl bcmath gd

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copy application files
COPY . /app

# Install dependencies
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Create SQLite database if not exists
RUN touch /app/database/database.sqlite

# Set permissions
RUN chmod -R 775 /app/storage /app/bootstrap/cache

# Expose port
EXPOSE 8080

# Start application
CMD php artisan serve --host=0.0.0.0 --port=8080
