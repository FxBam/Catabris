FROM php:8.2-apache

# Installation des extensions PHP nécessaires
RUN docker-php-ext-install pdo pdo_mysql mysqli

# Copie de tout le projet à la racine
COPY . /var/www/

# Changer le DocumentRoot pour pointer vers /var/www/www au lieu de /var/www/html
ENV APACHE_DOCUMENT_ROOT /var/www/www

RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

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