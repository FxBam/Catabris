FROM php:8.1-apache

# Install PDO MySQL
RUN apt-get update && apt-get install -y \
    libzip-dev \
    unzip \
    && docker-php-ext-install pdo pdo_mysql

# Copy application files
COPY . /var/www/html/

RUN chown -R www-data:www-data /var/www/html && a2enmod rewrite

EXPOSE 80

CMD ["apache2-foreground"]
FROM php:8.2-apache

# Installation des extensions PHP nécessaires
RUN docker-php-ext-install pdo pdo_mysql mysqli

# Copie de tout le projet à la racine
COPY . /var/www/

# Créer un lien symbolique du dossier api dans www pour qu'il soit accessible
RUN ln -s /var/www/api /var/www/www/api

# Changer le DocumentRoot pour pointer vers /var/www/www
ENV APACHE_DOCUMENT_ROOT /var/www/www

RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# Configuration des permissions
RUN chown -R www-data:www-data /var/www \
    && a2enmod rewrite

# Configuration pour permettre les .htaccess et suivre les liens symboliques
RUN echo '<Directory /var/www/www>\n\
    Options Indexes FollowSymLinks\n\
    AllowOverride All\n\
    Require all granted\n\
</Directory>' >> /etc/apache2/apache2.conf

EXPOSE 80

WORKDIR /var/www/www