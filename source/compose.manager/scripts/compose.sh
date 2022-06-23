#!/bin/bash

export HOME=/root

case $1 in

  up)
    cd $2
    docker compose -p "$3" up -d 2>&1
    ;;

  down)
    cd $2
    docker compose -p "$3" down  2>&1
    ;;

  pull)
    cd $2
    docker compose -p "$3" pull  2>&1
    ;;

  stop)
    cd $2
    docker compose -p "$3" stop  2>&1
    ;;

  list) 
    docker compose ls -a --format json 2>&1
    ;;

  logs)
    cd $2
    docker compose logs -f 2>&1
    ;;

  *)
    echo -n "unknown command"
    ;;
esac