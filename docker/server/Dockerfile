FROM amazonlinux:2023
LABEL maintainer="vagnercardosoweb@gmail.com"
LABEL server="Slim4 Skeleton"

# Environments
ENV TZ=${TZ:-UTC}
ENV WORKDIR=${WORKDIR:-/var/www}

# Update system and install utils
RUN yum update -y && \
    yum -y install \
    make \
    gcc \
    re2c \
    gcc-c++ \
    vim \
    tar \
    zip \
    less \
    unzip \
    rsync \
    nginx \
    git \
    sudo && \
    rm -rf /var/cache/apk/*

# Install nodejs 20.x
RUN yum install https://rpm.nodesource.com/pub_20.x/nodistro/repo/nodesource-release-nodistro-1.noarch.rpm -y
RUN yum install nodejs -y --setopt=nodesource-nodejs.module_hotfixes=1

# Install node and php extensions
RUN yum install -y \
    nodejs \
    unixODBC-devel \
    php-fpm \
    php-pear \
    php-devel \
    php-pdo \
    php-pgsql \
    php-exif \
    php-soap \
    php-mbstring \
    php-xml \
    php-iconv \
    php-cli \
    php-zip \
    php-mysqli \
    php-xml \
    php-sodium \
    php-gd && \
    rm -rf /var/cache/apk/*

# Install pecl extensions
RUN pecl channel-update pecl.php.net
RUN pecl -v install redis xdebug mongodb sqlsrv pdo_sqlsrv

# Install composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copy configs
COPY ./docker-entrypoint /usr/bin/docker-entrypoint
COPY ./etc /etc

# Create folder and permissions
RUN mkdir /run/php-fpm
RUN mkdir -p ${WORKDIR}
RUN chown 1000.1000 ${WORKDIR}
RUN usermod -u 1000 nginx
RUN groupmod -g 1000 nginx

USER nginx
EXPOSE 80 443
WORKDIR ${WORKDIR}
CMD ["docker-entrypoint"]
