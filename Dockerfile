FROM php:8.2-apache

# Installation des extensions PHP nécessaires
RUN docker-php-ext-install pdo pdo_mysql mysqli

# Copie de tout le projet
COPY . /var/www/

# Le DocumentRoot d'Apache doit pointer vers /var/www/www (ton dossier www)
RUN sed -i 's|/var/www/html|/var/www/www|g' /etc/apache2/sites-available/000-default.conf \
    && sed -i 's|/var/www/html|/var/www/www|g' /etc/apache2/apache2.conf

# Configuration des permissions
RUN chown -R www-data:www-data /var/www \
    && a2enmod rewrite

# Configuration pour permettre les .htaccess
RUN echo '<Directory /var/www/www>\n\
    Options Indexes FollowSymLinks\n\
    AllowOverride All\n\
    Require all granted\n\
</Directory>' >> /etc/apache2/apache2.conf

EXPOSE 80

WORKDIR /var/www/www
