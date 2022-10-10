#!/bin/bash
export HOME=/root

SHORT=c:,f:,p:,d:,o:
LONG=command:,file:,project_name:,project_dir:,override:,debug
OPTS=$(getopt -a -n compose --options $SHORT --longoptions $LONG -- "$@")

eval set -- "$OPTS"

files=""
project_dir=""
debug=false

while :
do
  case "$1" in
    -c | --command )
      command="$2"
      shift 2
      ;;
    -f | --file )
      files="${files} -f ${2@Q}"
      shift 2
      ;;
    -p | --project_name )
      name="$2"
      shift 2
      ;;
    -d | --project_dir )
      if [ -d "$2" ]; then
        for file in $( find $2 -maxdepth 1 -type f -name '*compose*.yml' ); do
          files="$files -f ${file@Q}"
        done
      fi
      shift 2
      ;;
    --debug )
      debug=true
      shift;
      ;;
    --)
      shift;
      break
      ;;
    *)
      echo "Unexpected option: $1"
      ;;
  esac
done

case $command in

  up)
    if [ "$debug" = true ]; then
      logger "docker compose $files -p "$name" up -d"
    fi
    eval docker compose $files -p "$name" up -d 2>&1
    ;;

  down)
    if [ "$debug" = true ]; then
      logger "docker compose $files -p "$name" down"
    fi
    eval docker compose $files -p "$name" down  2>&1
    ;;

  update)
    if [ "$debug" = true ]; then
      logger "docker compose $files -p "$name" pull"
      logger "docker compose $files -p "$name" up -d --build"
    fi 
    eval docker compose $files -p "$name" pull 2>&1
    eval docker compose $files -p "$name" up -d --build 2>&1
    ;;

  stop)
    if [ "$debug" = true ]; then
      logger "docker compose $files -p "$name" stop"
    fi
    eval docker compose $files -p "$name" stop  2>&1
    ;;

  list) 
    if [ "$debug" = true ]; then
      logger "docker compose ls -a --format json"
    fi
    eval docker compose ls -a --format json 2>&1
    ;;

  logs)
    if [ "$debug" = true ]; then
      logger "docker compose $files logs -f"
    fi
    eval docker compose $files logs -f 2>&1
    ;;

  *)
    echo "unknown command"
    echo $command 
    echo $name 
    echo $files
    ;;
esac