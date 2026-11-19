FROM php:8.2-apache

# Installer les extensions nécessaires pour MySQL/PostgreSQL et Composer
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libpq-dev \
    && docker-php-ext-install pdo pdo_mysql pdo_pgsql

# Activer mod_rewrite pour Apache
RUN a2enmod rewrite

# Copier Composer depuis l'image officielle
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Définir le dossier de travail
WORKDIR /var/www/html

# Copier les fichiers du projet
COPY . .

# Installer les dépendances PHP
RUN composer install --no-dev --optimize-autoloader

# Configurer Apache pour pointer sur le bon dossier (si tes fichiers API sont à la racine)
RUN sed -i 's!/var/www/html!/var/www/html/api!g' /etc/apache2/sites-available/000-default.conf