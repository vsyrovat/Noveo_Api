#!/usr/bin/env bash

export UID=`id -u` 2>/dev/null
export GID=`id -g`
docker-compose -f docker-compose.dev.yml down --remove-orphans
