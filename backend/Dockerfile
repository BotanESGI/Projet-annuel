FROM php:8.1-apache

# Installer les dépendances système et extensions PHP
RUN apt-get update && apt-get install -y \
    git unzip libicu-dev libonig-dev libzip-dev zip curl \
    && docker-php-ext-install intl pdo pdo_mysql zip \
    && a2enmod rewrite

# Installer Composer depuis l'image officielle
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Définir le répertoire de travail
WORKDIR /var/www/html

# Copier les fichiers du projet dans le container
COPY . .

# Modifier la propriété des fichiers pour l'utilisateur Apache
RUN chown -R www-data:www-data /var/www/html

# Modifier le DocumentRoot d'Apache vers /var/www/html/public
RUN sed -i 's|DocumentRoot /var/www/html|DocumentRoot /var/www/html/public|' /etc/apache2/sites-available/000-default.conf

# Installer les dépendances PHP via Composer
RUN composer install --no-interaction --prefer-dist

# Exposer le port 80
EXPOSE 80
