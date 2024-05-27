FROM php:8.2-apache

RUN apt-get update -y && apt-get install -y libmcrypt-dev

RUN apt-get update && \
    apt-get install -y --no-install-recommends libssl-dev zlib1g-dev curl git unzip netcat-traditional libxml2-dev libpq-dev libzip-dev && \
    pecl install apcu && \
    docker-php-ext-configure pgsql -with-pgsql=/usr/local/pgsql && \
    docker-php-ext-install -j$(nproc) zip opcache intl pdo_pgsql pgsql ldap && \
    docker-php-ext-enable apcu pdo_pgsql sodium && \
    apt-get clean && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*

RUN a2enmod rewrite
WORKDIR /var/www/html
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
COPY . .
RUN composer install

ENV DATABASE_URL="postgresql://postgres:mysecretpassword@database:5432/yearbook?serverVersion=16&charset=utf8"
RUN touch .env
RUN chmod +x initEnv.sh

ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
ENV APACHE_RUN_USER=www-data
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf
RUN mv -f ./000-default.conf /etc/apache2/sites-enabled/000-default.conf
RUN chown -R www-data:www-data /etc/apache2/sites-enabled/000-default.conf
RUN chown -R www-data:www-data /var/www/html
EXPOSE 80
CMD ./initEnv.sh && composer require symfony/runtime && php bin/console doctrine:migrations:migrate --no-interaction && chown -R www-data:www-data /var/www/html/var && apache2-foreground