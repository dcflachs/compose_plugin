#!/bin/bash
[ -z "$OUTPUT_FOLDER" ] && echo "Output Folder not set" && exit 1
[ -z "$COMPOSE_VERSION" ] && echo "Compose Version not set" && exit 2
[ -z "$COMPOSE_SWITCH_VERSION" ] && echo "Compose Switch Version not set" && exit 3
tmpdir=/tmp/tmp.$(( $RANDOM * 19318203981230 + 40 ))
version=$(date +"%Y.%m.%d")$1

mkdir -p $tmpdir

# mkdir -p $tmpdir/usr/local/emhttp/plugins/compose.manager

# cp -RT /mnt/source/docker.compose/ $tmpdir/usr/local/emhttp/plugins/compose.manager/

cd $tmpdir

#Install the docker compose cli plugin
wget --no-check-certificate https://github.com/docker/compose/releases/download/v${COMPOSE_VERSION}/docker-compose-linux-x86_64
wget --no-check-certificate https://github.com/docker/compose/releases/download/v${COMPOSE_VERSION}/docker-compose-linux-x86_64.sha256
sha256sum -c docker-compose-linux-x86_64.sha256 2>&1 | grep -q OK || exit 4
rm docker-compose-linux-x86_64.sha256

mkdir -p $tmpdir/usr/local/lib/docker/cli-plugins/
cp docker-compose-linux-x86_64 $tmpdir/usr/local/lib/docker/cli-plugins/docker-compose
chmod -R +x $tmpdir/usr/local/lib/docker/cli-plugins/
rm docker-compose-linux-x86_64

#Install compose switch
wget --no-check-certificate  https://github.com/docker/compose-switch/releases/download/v${COMPOSE_SWITCH_VERSION}/docker-compose-linux-amd64 
mkdir -p $tmpdir/usr/local/bin
cp docker-compose-linux-amd64 $tmpdir/usr/local/bin/docker-compose
chmod +x $tmpdir/usr/local/bin/docker-compose
rm docker-compose-linux-amd64

makepkg -l y -c y $OUTPUT_FOLDER/compose.manager-package-${version}.txz

cd /

echo "MD5:"

md5sum $OUTPUT_FOLDER/compose.manager-package-${version}.txz