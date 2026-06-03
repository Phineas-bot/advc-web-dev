FROM php:8.2-apache

# Install system deps and PDO MySQL
RUN apt-get update \
  && DEBIAN_FRONTEND=noninteractive apt-get install -y --no-install-recommends \
    default-libmysqlclient-dev \
    libzip-dev \
    zip \
    unzip \
  && docker-php-ext-install pdo_mysql \
  && a2enmod rewrite \
  && rm -rf /var/lib/apt/lists/*

WORKDIR /var/www/html

# Copy app into container (avoid copying Dockerfiles/scripts accidentally)
COPY . /var/www/html

RUN chown -R www-data:www-data /var/www/html \
  && find /var/www/html -type d -exec chmod 755 {} + \
  && find /var/www/html -type f -exec chmod 644 {} +

EXPOSE 80

CMD ["apache2-foreground"]
