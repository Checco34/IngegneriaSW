# Usa un'immagine ufficiale di PHP con Apache
FROM php:8.2-apache

# Abilita mod_rewrite di Apache per URL "puliti" (pretty URLs)
RUN a2enmod rewrite

# Installa le estensioni PHP necessarie per connettersi a MySQL
RUN docker-php-ext-install pdo pdo_mysql

# Copia i file di configurazione di Apache (se necessario)
# COPY apache-config.conf /etc/apache2/sites-available/000-default.conf

# Imposta la directory di lavoro
WORKDIR /var/www/html

# Copia i file del progetto nella document root del container
COPY ./public /var/www/html/public
COPY ./src /var/www/html/src
