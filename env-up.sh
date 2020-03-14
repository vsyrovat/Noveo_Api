#!/usr/bin/env bash

mkdir var/pgdata
export UID=`id -u`
export GID=`id -g`
docker-compose -f docker-compose.dev.yml up -d
