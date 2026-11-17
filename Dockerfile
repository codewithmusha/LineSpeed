
FROM php:8.3-apache

RUN a2enmod rewrite headers \
    && sed -i 's/Listen 80/Listen 10000/' /etc/apache2/ports.conf \
    && sed -i 's/<VirtualHost \*:80>/<VirtualHost *:10000>/' /etc/apache2/sites-available/000-default.conf \
    && printf '<Directory /var/www/html>\n    AllowOverride All\n    Require all granted\n</Directory>\n' > /etc/apache2/conf-available/linespeed.conf \
    && a2enconf linespeed

WORKDIR /var/www/html
COPY . /var/www/html/
ENV PORT=10000
EXPOSE 10000
CMD ["apache2-foreground"]
