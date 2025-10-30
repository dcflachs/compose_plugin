#!/bin/bash
[ -z "$COMPOSE_VERSION" ] && COMPOSE_VERSION=2.40.3
[ -z "$COMPOSE_SWITCH_VERSION" ] && COMPOSE_SWITCH_VERSION=1.0.5
[ -z "$ACE_VERSION" ] && ACE_VERSION=1.4.14
[ -z "$SWEETALERT_VERSION" ] && SWEETALERT_VERSION=2.1.2
docker run --rm --tmpfs /tmp -v $PWD/archive:/mnt/output:rw -e TZ="America/New_York" -e COMPOSE_VERSION=$COMPOSE_VERSION -e COMPOSE_SWITCH_VERSION=$COMPOSE_SWITCH_VERSION -e ACE_VERSION=$ACE_VERSION -e SWEETALERT_VERSION=$SWEETALERT_VERSION -e OUTPUT_FOLDER="/mnt/output" -v $PWD/source:/mnt/source:ro vbatts/slackware:latest /mnt/source/pkg_build.sh $UI_VERSION_LETTER
