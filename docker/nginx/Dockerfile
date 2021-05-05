FROM nginx:1.19.7-alpine

# Environements
ENV WORKDIR=/home/app
ENV TZ=America/Sao_Paulo

RUN apk add --update --no-cache tzdata

# Copy files to container
COPY ./certs /etc/certs
COPY ./nginx.conf /etc/nginx/conf.d/default.conf

WORKDIR ${WORKDIR}
