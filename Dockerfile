FROM php:7.4-fpm

MAINTAINER Vagner Cardoso <vagnercardosoweb@gmail.com>

# Update system and install required libs
RUN apt-get update -yq \
    && apt-get install -yq \
       zlib1g-dev \
       libpng-dev \
       libxml2-dev \
       libonig-dev \
       freetds-common \
       libpq-dev \
       freetds-dev \
       freetds-bin \
       zip \
       unzip \
       git \
       curl \
    && apt-get upgrade -yq \
    && apt-get autoremove -yq \
    && apt-get autoclean -yq \
    && rm -rf /var/lib/apt/lists/* \
              /tmp/* \
              /var/tmp/* \
              /usr/share/doc/*

# Configure php extensions
RUN docker-php-ext-configure pdo_dblib --with-libdir=/lib/x86_64-linux-gnu
RUN docker-php-ext-configure soap --enable-soap

# Install php extensions
RUN docker-php-ext-install \
    gd \
    json \
    iconv \
    gd \
    bcmath \
    xml \
    mbstring \
    pdo \
    pdo_mysql \
    pdo_pgsql \
    pdo_dblib \
    opcache \
    intl \
    exif \
    soap

# Install xdebug
RUN pecl install xdebug
RUN echo "xdebug.remote_enable=1" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini &&\
    echo "xdebug.remote_connect_back=1" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini &&\
    echo "xdebug.idekey=\"PHPSTORM\"" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini &&\
    echo "xdebug.remote_port=9001" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini

# Enable php extensions
RUN docker-php-ext-enable gd xdebug mbstring intl pdo_dblib bcmath exif iconv

# Install composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Create php.ini
RUN mv "${PHP_INI_DIR}/php.ini-development" "${PHP_INI_DIR}/php.ini"

# Start project
CMD php -S 0.0.0.0:8080 -t ./public_html
