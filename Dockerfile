# Usa una imagen base de PHP con la versión adecuada
FROM php:8.2-fpm

# Instala dependencias del sistema necesarias para Laravel
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd

# Instala Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Establece el directorio de trabajo
WORKDIR /var/www

# Copia los archivos del proyecto al contenedor
COPY . .

# Instala las dependencias de PHP con Composer
RUN composer install --optimize-autoloader --no-dev

# Copia el archivo .env.example a .env
RUN cp .env.example .env

# Genera la clave de aplicación
RUN php artisan key:generate --force

# Ejecuta las migraciones
RUN php artisan migrate --force

# Da permisos al directorio de almacenamiento
RUN chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache
RUN chmod -R 775 /var/www/storage /var/www/bootstrap/cache

# Expone el puerto 10000 para Render
EXPOSE 10000

# Inicia el servidor de Laravel
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=10000"]
