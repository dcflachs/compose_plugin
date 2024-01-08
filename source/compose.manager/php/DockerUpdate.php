<?PHP
/* This file and functionality is based on and uses the Dynamix Docker Manager from unRaid
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

define("SHELL_FORMAT", [
  'green' => "\033[32m",
  'yellow' => "\033[33m",
  'red' => "\033[31m",
  'bold' => "\033[1m",
  'default' => "\033[0m",
]);

$images = array();

// Take stdin from compose.sh and put into images array
$f = fopen( 'php://stdin', 'r' );
while( $image = fgets( $f ) )
  $images[] = trim($image);
fclose( $f );
$images = array_unique($images);


echo "\nChecking for updates & updating unRaid's image version details for " . SHELL_FORMAT['bold'] . count($images) . " image(s):\n" . SHELL_FORMAT['default'];
$DockerUpdate = new DockerUpdate();

try {
  foreach( $images as $image ) {
      echo " - {$image}...";

      // Update the local image version info
      $localVer = $DockerUpdate->inspectLocalVersion($image);
      $DockerUpdate->setUpdateStatus($image, $localVer);

      // Update the remote version info
      $DockerUpdate->reloadUpdateStatus($image);

      // Get current update status - true=up-to-date  false=update available  null=data unavailable
      $updateStatus = $DockerUpdate->getUpdateStatus($image);
      echo ( $updateStatus==true ? SHELL_FORMAT['green']." up to date" : ( $updateStatus===null ? SHELL_FORMAT['red']." failed to get update status" : SHELL_FORMAT['yellow']." update available" ) ) . "\n" . SHELL_FORMAT['default'];
  }
  echo "\nunRaid image versions updated\n";

} catch (Exception $err) {
  echo SHELL_FORMAT['red']."\nUpdating unRaid's image versions failed".SHELL_FORMAT['default']."\nError: ". $err->getMessage() ."\n";
}
?>