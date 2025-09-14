# Usa un'immagine ufficiale di PHP con Apache
FROM php:8.2-apache

# Installa le estensioni PHP necessarie per connettersi a MySQL
RUN docker-php-ext-install pdo pdo_mysql

# Abilita mod_rewrite di Apache per gli URL "puliti"
RUN a2enmod rewrite

# Copia l'intero progetto nella root del server web del container
COPY . /var/www/html/

# Imposta la DocumentRoot sulla cartella /public per sicurezza
RUN sed -ri -e 's!/var/www/html!/var/www/html/public!g' /etc/apache2/sites-available/*.conf