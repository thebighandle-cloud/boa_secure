FROM php:8.2-apache

WORKDIR /var/www/html

# Copy all files
COPY . /var/www/html/

# boa-api.php must be available as api.php (oatest.html expects api.php)
COPY boa-api.php api.php

# Ensure Apache can serve the directory (index.html now exists)
RUN chown -R www-data:www-data /var/www/html && chmod -R 755 /var/www/html

EXPOSE 80
