#!/bin/bash
[ -z "$OUTPUT_FOLDER" ] && echo "Output Folder not set" && exit 1
[ -z "$COMPOSE_VERSION" ] && echo "Compose Version not set" && exit 1
tmpdir=/tmp/tmp.$(( $RANDOM * 19318203981230 + 40 ))
version=$(date +"%Y.%m.%d")$1

# mkdir -p $tmpdir/usr/local/emhttp/plugins/compose.manager

# cp -RT /mnt/source/docker.compose/ $tmpdir/usr/local/emhttp/plugins/compose.manager/

mkdir -p $tmpdir/usr/local/lib/docker/cli-plugins/
cd $tmpdir/usr/local/lib/docker/cli-plugins/
wget https://github.com/docker/compose/releases/download/v${COMPOSE_VERSION}/docker-compose-linux-x86_64
wget https://github.com/docker/compose/releases/download/v${COMPOSE_VERSION}/docker-compose-linux-x86_64.sha256
sha256sum -c SHA256SUMS 2>&1 | grep -q OK || exit 2
rm docker-compose-linux-x86_64.sha256

cd $tmpdir

chmod -R +x $tmpdir/usr/local/lib/docker/cli-plugins/

makepkg -l y -c y $OUTPUT_FOLDER/compose.manager-package-${version}.txz

cd /

rm -rf $tmpdir

echo "MD5:"

md5sum $OUTPUT_FOLDER/compose.manager-package-${version}.txz