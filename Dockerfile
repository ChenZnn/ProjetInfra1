# Utilise une image officielle PHP avec Apache
FROM php:8.1-apache

# Copie le code source dans le dossier HTML du conteneur
COPY . /var/www/html/

RUN docker-php-ext-install mysqli pdo pdo_mysql
# Donne les bons droits
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

# Expose le port 80
EXPOSE 80
