#!/bin/bash
export HOME=/root

SHORT=c:,f:,p:,d:,o:
LONG=command:,file:,project_name:,project_dir:,override:
OPTS=$(getopt -a -n compose --options $SHORT --longoptions $LONG -- "$@")

eval set -- "$OPTS"

files=""
project_dir=""

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
    eval docker compose $files -p "$name" up -d 2>&1
    ;;

  down)
    eval docker compose $files -p "$name" down  2>&1
    ;;

  update)
    eval docker compose $files -p "$name" up -d --pull always --build 2>&1
    ;;

  stop)
    eval docker compose $files -p "$name" stop  2>&1
    ;;

  list) 
    eval docker compose ls -a --format json 2>&1
    ;;

  logs)
    eval docker compose $files logs -f 2>&1
    ;;

  *)
    echo "unknown command"
    echo $command 
    echo $name 
    echo $files
    ;;
esac