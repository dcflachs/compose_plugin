#!/bin/bash
SHORT=rt
OPTS=$(getopt -a -n patch_ui --options $SHORT -- "$@")

if [ $? -ne 0 ]
then
    exit
fi

eval set -- "$OPTS"

command="apply"

while :
do
  case "$1" in
    -r)
      command="remove"
      shift 1
      ;;
    --)
      shift;
      break
      ;;
    *)
      echo "Unexpected option: $1"
      exit 1
      ;;
  esac
done

patch_folder="/usr/local/emhttp/plugins/compose.manager/patches"
if [ ! -z $(grep -Po "6.10.\d+" /etc/unraid-version ) ]; then
  patch_folder="$patch_folder/6_10"
elif [ ! -z $(grep -Po "6.9.2" /etc/unraid-version ) ]; then
  patch_folder="$patch_folder/6_9"
elif [ ! -z $(grep -Po "6.11.\d+" /etc/unraid-version ) ]; then
  patch_folder="$patch_folder/6_11"
else
  exit 0
fi

docker_client_path="/usr/local/emhttp/plugins/dynamix.docker.manager/include"
docker_client_file="DockerClient.php"
case $command in

  apply)
    patch -s -N -r - -b -Y . -z .orig "$docker_client_path/$docker_client_file" "$patch_folder/docker_client.patch" 2>&1 || \
    mv "$docker_client_path/.$docker_client_file.orig" "$docker_client_path/$docker_client_file" 2>&1
    ;;

  remove)
    if [ -f "$docker_client_path/.$docker_client_file.orig" ]; then
      mv "$docker_client_path/.$docker_client_file.orig" "$docker_client_path/$docker_client_file" #2>&1
    fi
    ;;

  *)
    echo "unknown command"
    echo $command 
    echo $name 
    echo $files
    ;;
esac