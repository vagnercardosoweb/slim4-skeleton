FROM nginx:1.19.7-alpine

ENV TZ=America/Sao_Paulo
ENV APP_ROOT=/home/app
ENV SERVER_NAME=localhost
ENV DOCUMENT_ROOT=/home/app/public_html
ENV NGINX_LOG_DIR=${APP_ROOT}/docker/nginx/logs

WORKDIR ${APP_ROOT}

RUN apk add --update --no-cache tzdata

COPY ./nginx.conf /etc/nginx/conf.d/default.conf
