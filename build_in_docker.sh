#!/bin/bash

docker run --rm --tmpfs /tmp -v $PWD/archive:/mnt/output:rw -e OUTPUT_FOLDER="/mnt/output" -v $PWD/source:/mnt/source:ro vbatts/slackware:latest /mnt/source/pkg_build.sh $UI_VERSION_LETTER
