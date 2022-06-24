#!/bin/bash

export HOME=/root
export DOCKER_JSON=/usr/local/emhttp/state/plugins/dynamix.docker.manager/docker.json

case $1 in

  up)
    docker compose -f "$2" -p "$3" ps -a |
      awk '{if (NR!=1) {printf("%s.\"%s\"", sep, $1); sep=", "}}' |
      xargs -0 -I {} jq 'del({})' $DOCKER_JSON > $DOCKER_JSON
    docker compose -f "$2" -p "$3" up -d 2>&1
    ;;

  down)
    docker compose -f "$2" -p "$3" ps -a |
      awk '{if (NR!=1) {printf("%s.\"%s\"", sep, $1); sep=", "}}' |
      xargs -0 -I {} jq 'del({})' $DOCKER_JSON > $DOCKER_JSON
    docker compose -f "$2" -p "$3" down  2>&1
    ;;

  pull)
    docker compose -f "$2" -p "$3" pull  2>&1
    ;;

  stop)
    docker compose -f "$2" -p "$3" stop  2>&1
    ;;

  list) 
    docker compose ls -a --format json 2>&1
    ;;

  logs)
    docker compose -f "$2" logs -f 2>&1
    ;;

  *)
    echo -n "unknown command"
    ;;
esac