# Utilise une image officielle PHP avec Apache
FROM php:8.2-apache

# Installe les extensions nécessaires, notamment pdo_mysql
RUN docker-php-ext-install pdo pdo_mysql mysqli

# Active mod_rewrite (utile pour les routes propres)
RUN a2enmod rewrite

# Copie tous les fichiers du projet dans le dossier du serveur Apache
COPY . /var/www/html/

# Donne les bons droits (optionnel mais recommandé)
RUN chown -R www-data:www-data /var/www/html

# Expose le port 80 (port par défaut d'Apache)
EXPOSE 80
