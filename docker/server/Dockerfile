FROM amazonlinux:2
LABEL maintainer="vagnercardosoweb@gmail.com"
LABEL server="Slim4 Skeleton"

# Environments
ENV WORKDIR=/var/www/app.dev
ENV NPM_CONFIG_PREFIX=${WORKDIR}/.npm-global
ENV TZ=America/Sao_Paulo

WORKDIR ${WORKDIR}

RUN amazon-linux-extras install nginx1 php8.0
RUN curl -sL https://rpm.nodesource.com/setup_14.x | bash -

# Updates and installs system dependencies
RUN yum update -y && \
    yum install -y \
    vim \
    tar \
    zip \
    unzip \
    php-pdo \
    php-pgsql \
    php-opcache \
    php-xls \
    php-exif \
    php-soap \
    php-mbstring \
    php-xml \
    php-bcmatch \
    php-iconv \
    php-sybase \
    php-cli \
    php-zip \
    php-mysqli \
    php-xml \
    php-gd \
    php-rar \
    php-xdebug \
    php-mongodb \
    php-redis \
    nodejs

# Install composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Copy files to container
COPY ./etc /etc
COPY ./docker-entrypoint /usr/bin/docker-entrypoint

# Configure the environment
RUN mkdir -p ${WORKDIR}
RUN chown 1000.1000 ${WORKDIR}
RUN usermod -u 1000 nginx
RUN groupmod -g 1000 nginx

USER root
EXPOSE 80 443
CMD ["docker-entrypoint"]
