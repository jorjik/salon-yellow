FROM php:8.3-apache

RUN a2enmod rewrite headers \
 && printf '%s\n' \
    '<Directory /var/www/html>' \
    '    AllowOverride All' \
    '    Require all granted' \
    '</Directory>' \
    > /etc/apache2/conf-available/salon-override.conf \
 && a2enconf salon-override

COPY . /var/www/html/

RUN chown -R www-data:www-data /var/www/html

EXPOSE 80
