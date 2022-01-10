FROM amazonlinux:2
LABEL maintainer="vagnercardosoweb@gmail.com"
LABEL server="Slim4 Skeleton"

# Environments
ENV WORKDIR=/var/www/app.dev
ENV TZ=America/Sao_Paulo

WORKDIR ${WORKDIR}

RUN amazon-linux-extras install nginx1 php8.0
RUN curl -sL https://rpm.nodesource.com/setup_14.x | bash -

# Updates and installs system dependencies
RUN yum update -y && \
  yum install -y \
  gcc \
  re2c \
  gcc-c++ \
  unixODBC-devel \
  vim \
  tar \
  zip \
  less \
  unzip \
  php-pear \
  php-devel \
  php-pdo \
  php-pgsql \
  php-opcache \
  php-exif \
  php-soap \
  php-mbstring \
  php-xml \
  php-iconv \
  php-cli \
  php-zip \
  php-mysqli \
  php-xml \
  php-gd \
  nodejs

# Install pecl extensions
RUN pecl channel-update pecl.php.net
RUN pecl -v install rar redis xdebug mongodb sqlsrv pdo_sqlsrv

# Install composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Copy files to container
COPY ./etc /etc
COPY ./docker-entrypoint /usr/bin/docker-entrypoint

# RUN sed -i 's/daemon off/daemon on/g' /etc/nginx/nginx.conf

# Permissions Setup
RUN mkdir -p ${WORKDIR}
RUN chown 1000.1000 ${WORKDIR}
RUN usermod -u 1000 nginx
RUN groupmod -g 1000 nginx

USER root
EXPOSE 80 443
CMD ["docker-entrypoint"]
