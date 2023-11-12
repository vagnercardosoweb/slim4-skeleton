#!/usr/bin/env bash

docker-compose -f docker/server/docker-compose.yml up -d
docker logs slim4.server -f
