#!/bin/bash
export HOME=/root

SHORT=c:,f:,p:,d:,o:
LONG=command:,file:,project_name:,project_dir:,override:,debug,recreate
OPTS=$(getopt -a -n compose --options $SHORT --longoptions $LONG -- "$@")

eval set -- "$OPTS"

files=""
project_dir=""
other_options=""
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
    --recreate )
      other_options="--force-recreate"
      shift;
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
      logger "docker compose $files -p "$name" up $other_options -d"
    fi
    eval docker compose $files -p "$name" up $other_options -d 2>&1
    ;;

  down)
    if [ "$debug" = true ]; then
      logger "docker compose $files -p "$name" down"
    fi
    eval docker compose $files -p "$name" down  2>&1
    ;;
    
  update)
    if [ "$debug" = true ]; then
      logger "docker compose $files -p "$name" images -q"
      logger "docker compose $files -p "$name" pull"
      logger "docker compose $files -p "$name" up -d --build"
    fi

    images=()
    images+=( $(docker compose $files -p "$name" images -q) )

    if [ ${#images[@]} -eq 0 ]; then   
      delete="-f"
      files_arr=( $files ) 
      files_arr=( ${files_arr[@]/$delete} )
      if (( ${#files_arr[@]} )); then
        services=( $(cat ${files_arr[*]//\'/} | sed -n 's/image:\(.*\)/\1/p') )

        for image in "${services[@]}"; do
          images+=( $(docker images -q --no-trunc ${image}) )
        done
      fi

      images=( ${images[*]##sha256:} )
    fi
    
    eval docker compose $files -p "$name" pull 2>&1
    eval docker compose $files -p "$name" up -d --build 2>&1

    new_images=( $(docker compose $files -p "$name" images -q) )
    for target in "${new_images[@]}"; do
      for i in "${!images[@]}"; do
        if [[ ${images[i]} = $target ]]; then
          unset 'images[i]'
        fi
      done
    done

    if (( ${#images[@]} )); then
      if [ "$debug" = true ]; then
        logger "docker rmi ${images[*]}"
      fi
      eval docker rmi ${images[*]}
    fi
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