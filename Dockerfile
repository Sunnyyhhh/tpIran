FROM php:8.2-apache

# Installation des extensions PHP nécessaires
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    zip \
    unzip \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo pdo_mysql gd \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Activation du module Apache rewrite (pour .htaccess)
RUN a2enmod rewrite

# Autoriser le .htaccess dans le répertoire web
RUN sed -i 's/AllowOverride None/AllowOverride All/g' /etc/apache2/apache2.conf

# Dossier de travail
WORKDIR /var/www/html

# Copier tous les fichiers du projet dans le conteneur
COPY . /var/www/html/

# Permissions sur le dossier uploads
RUN mkdir -p /var/www/html/public/uploads \
    && chown -R www-data:www-data /var/www/html/public/uploads \
    && chmod -R 775 /var/www/html/public/uploads

# Exposer le port 80
EXPOSE 80
