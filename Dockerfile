FROM php:7.4-fpm

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
    && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/* /usr/share/doc/*

RUN docker-php-ext-configure pdo_dblib --with-libdir=/lib/x86_64-linux-gnu
RUN docker-php-ext-configure soap --enable-soap

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

RUN pecl install xdebug && docker-php-ext-enable xdebug

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

RUN mv "${PHP_INI_DIR}/php.ini-development" "${PHP_INI_DIR}/php.ini"

COPY . .

CMD php -S 0.0.0.0:8080 -t ./public_html
