# Utilise l’image officielle PHP avec Apache
FROM php:8.2-apache

# Installe les extensions nécessaires, dont PDO MySQL
RUN docker-php-ext-install pdo pdo_mysql

# Copie ton site dans le dossier Apache
COPY ./www/ /var/www/html/

# Copie ton API si elle est dans un dossier séparé
COPY ./api/ /var/www/html/api/

# Active mod_rewrite si nécessaire
RUN a2enmod rewrite

EXPOSE 80

COPY ./add_index.php /var/www/html/
