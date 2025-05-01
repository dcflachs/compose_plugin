#!/bin/bash
[ -z "$OUTPUT_FOLDER" ] && echo "Output Folder not set" && exit 1
[ -z "$COMPOSE_VERSION" ] && echo "Compose Version not set" && exit 2
[ -z "$COMPOSE_SWITCH_VERSION" ] && echo "Compose Switch Version not set" && exit 3
[ -z "$ACE_VERSION" ] && echo "ACE Version not set" && exit 4
[ -z "$SWEETALERT_VERSION" ] && echo "Sweetalert Version not set" && exit 5
tmpdir=/tmp/tmp.$(( $RANDOM * 19318203981230 + 40 ))
version=$(date +"%Y.%m.%d")$1

shopt -s extglob

#Install unzip build dependency
wget --no-check-certificate https://slackware.uk/slackware/slackware64-14.2/slackware64/a/infozip-6.0-x86_64-3.txz
upgradepkg --install-new infozip-6.0-x86_64-3.txz

mkdir -p $tmpdir

mkdir -p $tmpdir/usr/local/emhttp/plugins/compose.manager
cp -RT /mnt/source/compose.manager/ $tmpdir/usr/local/emhttp/plugins/compose.manager/

cd $tmpdir

chmod -R +x $tmpdir/usr/local/emhttp/plugins/compose.manager/event/
chmod -R +x $tmpdir/usr/local/emhttp/plugins/compose.manager/scripts/
chmod -R +x $tmpdir/usr/local/emhttp/plugins/compose.manager/php/

#Install the docker compose cli plugin
wget --no-check-certificate https://github.com/docker/compose/releases/download/v${COMPOSE_VERSION}/docker-compose-linux-x86_64
wget --no-check-certificate https://github.com/docker/compose/releases/download/v${COMPOSE_VERSION}/docker-compose-linux-x86_64.sha256
sha256sum -c docker-compose-linux-x86_64.sha256 2>&1 | grep -q OK || exit 4
rm docker-compose-linux-x86_64.sha256

mkdir -p $tmpdir/usr/lib/docker/cli-plugins/
cp docker-compose-linux-x86_64 $tmpdir/usr/lib/docker/cli-plugins/docker-compose
chmod -R +x $tmpdir/usr/lib/docker/cli-plugins/
rm docker-compose-linux-x86_64

#Install compose switch
wget --no-check-certificate  https://github.com/docker/compose-switch/releases/download/v${COMPOSE_SWITCH_VERSION}/docker-compose-linux-amd64 
mkdir -p $tmpdir/usr/local/bin
cp docker-compose-linux-amd64 $tmpdir/usr/local/bin/docker-compose
chmod +x $tmpdir/usr/local/bin/docker-compose
rm docker-compose-linux-amd64

#Install Ace Editor
mkdir -p $tmpdir/usr/local/emhttp/plugins/compose.manager/javascript/ace/
wget --no-check-certificate https://github.com/ajaxorg/ace-builds/archive/refs/tags/v${ACE_VERSION}.zip
mkdir -p /tmp/ace
unzip v${ACE_VERSION}.zip "ace-builds-${ACE_VERSION}/src-min-noconflict/*" -d "/tmp/ace"
cp /tmp/ace/ace-builds-${ACE_VERSION}/src-min-noconflict/!(mode-*|theme-*) $tmpdir/usr/local/emhttp/plugins/compose.manager/javascript/ace/
cp /tmp/ace/ace-builds-${ACE_VERSION}/src-min-noconflict/*yaml.js $tmpdir/usr/local/emhttp/plugins/compose.manager/javascript/ace/
cp /tmp/ace/ace-builds-${ACE_VERSION}/src-min-noconflict/*text.js $tmpdir/usr/local/emhttp/plugins/compose.manager/javascript/ace/

cp /tmp/ace/ace-builds-${ACE_VERSION}/src-min-noconflict/*tomorrow.js $tmpdir/usr/local/emhttp/plugins/compose.manager/javascript/ace/
cp /tmp/ace/ace-builds-${ACE_VERSION}/src-min-noconflict/*tomorrow_night.js $tmpdir/usr/local/emhttp/plugins/compose.manager/javascript/ace/

chmod -R +x $tmpdir/usr/local/emhttp/plugins/compose.manager/javascript/ace/
rm -R /tmp/ace
rm v${ACE_VERSION}.zip

#Install SweetAlert 2
wget --no-check-certificate https://unpkg.com/sweetalert@$SWEETALERT_VERSION/dist/sweetalert.min.js
mkdir -p $tmpdir/usr/local/emhttp/plugins/compose.manager/javascript/sweetalert
mv sweetalert.min.js $tmpdir/usr/local/emhttp/plugins/compose.manager/javascript/sweetalert/sweetalert2.min.js
chmod -R +x $tmpdir/usr/local/emhttp/plugins/compose.manager/javascript/sweetalert

#Modify SweetAlert 2 for compatability
sed -i'' 's/exports.swal/exports.swal2/' $tmpdir/usr/local/emhttp/plugins/compose.manager/javascript/sweetalert/sweetalert2.min.js
sed -i'' 's/t.swal/t.swal2/' $tmpdir/usr/local/emhttp/plugins/compose.manager/javascript/sweetalert/sweetalert2.min.js
sed -i'' 's/e.sweetAlert/e.sweetAlert2/' $tmpdir/usr/local/emhttp/plugins/compose.manager/javascript/sweetalert/sweetalert2.min.js
sed -i'' 's/e.swal/e.swal2/' $tmpdir/usr/local/emhttp/plugins/compose.manager/javascript/sweetalert/sweetalert2.min.js

makepkg -l y -c y $OUTPUT_FOLDER/compose.manager-package-${version}.txz

cd /

MD5=`md5sum $OUTPUT_FOLDER/compose.manager-package-${version}.txz`

echo "MD5: $MD5"

echo "Compose v${COMPOSE_VERSION}" >> $OUTPUT_FOLDER/release_info
echo "Compose Switch v${COMPOSE_SWITCH_VERSION}" >> $OUTPUT_FOLDER/release_info
echo "Ace v${ACE_VERSION}" >> $OUTPUT_FOLDER/release_info
echo "" >> $OUTPUT_FOLDER/release_info
echo "MD5: $(echo $MD5 | head -n1 | awk '{print $1;}')" >> $OUTPUT_FOLDER/release_info
