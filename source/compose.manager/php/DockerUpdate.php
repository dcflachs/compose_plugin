<?PHP
/* This file and functionality is based on  and uses the Dynamix Docker Danager from unRaid
 * Script by mtongnz, Jan 2024
 * 
 * Copyright 2005-2022, Lime Technology
 * Copyright 2014-2022, Guilherme Jardim, Eric Schultz, Jon Panozzo.
 * Copyright 2012-2022, Bergware International.
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License version 2,
 * as published by the Free Software Foundation.
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 */
?>
<?
$docroot = $docroot ?? $_SERVER['DOCUMENT_ROOT'] ?: '/usr/local/emhttp';
require_once "$docroot/plugins/dynamix.docker.manager/include/DockerClient.php";

$images = array();

// Take stdin from compose.sh and put into images array
$f = fopen( 'php://stdin', 'r' );
while( $image = fgets( $f ) )
  $images[] = trim($image);
fclose( $f );
$images = array_unique($images);


echo "\nUpdating unRaid's image version details for " . count($images) . " image(s):\n";
$DockerUpdate = new DockerUpdate();

foreach( $images as $image ) {
    echo " - updating " . $image . "\n";

    // Delete current info to force an update
    $updateStatus = DockerUtil::loadJSON($dockerManPaths['update-status']);
    $updateStatus[$image]['local'] = null;
    DockerUtil::saveJSON($dockerManPaths['update-status'], $updateStatus);

    // Update the version info
    $DockerUpdate->reloadUpdateStatus($image);
}

echo "unRaid image versions updated\n";
?>