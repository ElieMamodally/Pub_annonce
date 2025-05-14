FROM php:8.2-apache

# Copier tous les fichiers dans /var/www/html
COPY . /var/www/html/

# Activer mod_rewrite si nécessaire
RUN a2enmod rewrite

# Donner les bons droits
RUN chown -R www-data:www-data /var/www/html

EXPOSE 80
