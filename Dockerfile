FROM php:8.2.28-apache
ADD https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions /usr/local/bin/
RUN apt-get update && apt-get install -y imagemagick \
    && pecl install redis-5.3.7 \
    && docker-php-ext-enable redis \
    && apt-get install -y libmagickwand-dev  libicu-dev \
    && pecl install imagick \
    && docker-php-ext-enable imagick \
    && docker-php-ext-configure intl \
    && docker-php-ext-install intl


RUN apt-get update && apt-get install -y mariadb-client git unzip && rm -rf /var/lib/apt
RUN apt-get update && apt-get install ffmpeg -y



RUN a2enmod headers setenvif rewrite env




RUN a2enmod rewrite
RUN chmod +x /usr/local/bin/install-php-extensions && \
    install-php-extensions gd pdo pdo_mysql mysqli mbstring opcache zip soap



ENV TZ=Europe/Madrid
RUN apt-get update && apt-get install -y tzdata
RUN ln -snf /usr/share/zoneinfo/$TZ /etc/localtime && echo $TZ > /etc/timezone


# Instalar Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer



RUN apt-get update && apt-get install -y locales-all



WORKDIR /var/www/html/


RUN chown -R www-data:www-data /var/www/html/