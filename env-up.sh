#!/usr/bin/env bash

mkdir -p var/pgdata
export UID=`id -u` 2>/dev/null
export GID=`id -g`
docker-compose -f docker-compose.dev.yml up -d
