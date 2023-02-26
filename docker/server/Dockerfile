FROM amazonlinux:2
LABEL maintainer="vagnercardosoweb@gmail.com"
LABEL server="Slim4 Skeleton"

# Environments
ENV TZ=${TZ:-UTC}
ENV WORKDIR=${WORKDIR:-/var/www/app.dev}

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
    sudo && \
    rm -rf /var/cache/apk/*

# Install nginx and node16
RUN amazon-linux-extras install nginx1 postgresql10
RUN curl -sL https://rpm.nodesource.com/setup_16.x | bash -

# Install php 8.2 via https://rpms.remirepo.net/wizard/
RUN yum -y install https://dl.fedoraproject.org/pub/epel/epel-release-latest-7.noarch.rpm
RUN yum -y install https://rpms.remirepo.net/enterprise/remi-release-7.rpm
RUN yum -y install yum-utils rpl
RUN yum-config-manager --enable remi-php82
RUN rpl [remi-php82] "[remi-php82]\npriority=1" /etc/yum.repos.d/remi-php82.repo -e

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

# Copy composer and required files to container
COPY ./etc /etc
COPY ./docker-entrypoint /usr/bin/docker-entrypoint
COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer

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
