FROM php:8.1-apache

# Habilitar mod_rewrite para el ruteo
RUN a2enmod rewrite

# Instalar dependencias del sistema y extensiones de PHP necesarias
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    zip \
    unzip \
    git \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd pdo pdo_mysql

# Instalar Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Configurar el DocumentRoot a la carpeta public
ENV APACHE_DOCUMENT_ROOT /var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# Directorio de trabajo
WORKDIR /var/www/html

<<<<<<< HEAD
# Ajustar permisos para que Apache pueda escribir
RUN chown -R www-data:www-data /var/www/html
=======
# Copiar archivos del proyecto
COPY . .

# Instalar dependencias de Composer si existe composer.json
RUN if [ -f composer.json ]; then composer install --no-interaction --prefer-dist --optimize-autoloader; fi

# Ajustar permisos para Apache
RUN chown -R www-data:www-data /var/www/html

EXPOSE 80
>>>>>>> 207642dd7a1f4c040e06bb8d8198d87475613a1e
