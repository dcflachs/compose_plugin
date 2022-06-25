#!/bin/bash
export HOME=/root

SHORT=c:,f:,p:,d:,o:
LONG=command:,file:,project_name:,project_dir:,override:
OPTS=$(getopt -a -n compose --options $SHORT --longoptions $LONG -- "$@")

eval set -- "$OPTS"

files=""

while :
do
  case "$1" in
    -c | --command )
      command="$2"
      shift 2
      ;;
    -f | --file )
      files="-f $2"
      shift 2
      ;;
    -p | --project_name )
      name="$2"
      shift 2
      ;;
    -o | --override )
      override="$2"
      shift 2
      ;;
    -d | --project_dir )
      project_dir="$2"
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

if [ -d "$project_dir" ]; then
  for file in $( find $project_dir -maxdepth 1 -type f -name '*compose*.yml' ); do
    files="$files -f $file"
  done
fi

if [ -f "$override" ]; then
  files="$files -f $override"
fi

case $command in

  up)
    docker compose $files -p "$name" up -d 2>&1
    ;;

  down)
    docker compose $files -p "$name" down  2>&1
    ;;

  pull)
    docker compose $files -p "$name" pull  2>&1
    ;;

  stop)
    docker compose $files -p "$name" stop  2>&1
    ;;

  list) 
    docker compose ls -a --format json 2>&1
    ;;

  logs)
    docker compose $files logs -f 2>&1
    ;;

  *)
    echo "unknown command"
    echo $command 
    echo $name 
    echo $files
    ;;
esac