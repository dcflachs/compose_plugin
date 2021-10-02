#!/bin/bash
[ -z "$COMPOSE_VERSION" ] && COMPOSE_VERSION=2.0.1
docker run --rm --tmpfs /tmp -v $PWD/archive:/mnt/output:rw -e COMPOSE_VERSION=$COMPOSE_VERSION -e OUTPUT_FOLDER="/mnt/output" -v $PWD/source:/mnt/source:ro vbatts/slackware:latest /mnt/source/pkg_build.sh $UI_VERSION_LETTER
