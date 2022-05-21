<?php

require_once("/usr/local/emhttp/plugins/compose.manager/php/defines.php");

function getElement($element) {
    $return = str_replace(".","-",$element);
    $return = str_replace(" ","",$return);
    return $return;
  }

switch ($_POST['action']) {
    case 'addStack':
        $stackName = isset($_POST['stackName']) ? urldecode(($_POST['stackName'])) : "";
        $folderName = str_replace('"',"",$stackName);
        $folderName = str_replace("'","",$folderName);
        $folderName = str_replace("&","",$folderName);
        $folderName = str_replace("(","",$folderName);
        $folderName = str_replace(")","",$folderName);
        $folderName = preg_replace("/ {2,}/", " ", $folderName);
        $folder = "$compose_root/$folderName";
        while ( true ) {
          if ( is_dir($folder) ) {
            $folder .= mt_rand();
          } else {
            break;
          }
        }
        exec("mkdir -p ".escapeshellarg($folder));
        file_put_contents("$folder/compose.yml","version: '3'\nservices:\n");
        file_put_contents("$folder/name",$stackName);
        echo "ok";
        break;
    case 'deleteStack':
        $stackName = isset($_POST['stackName']) ? urldecode(($_POST['stackName'])) : "";
        if ( ! $stackName ) {
          echo "huh?";
          break;
        }
        $folderName = "$compose_root/$stackName";
        exec("rm -rf ".escapeshellarg($folderName));
        echo "deleted";
        break;
    case 'changeName':
        $script = isset($_POST['script']) ? urldecode(($_POST['script'])) : "";
        $newName = isset($_POST['newName']) ? urldecode(($_POST['newName'])) : "";
        file_put_contents("$compose_root/$script/name",trim($newName));
        echo "ok";
        break;
    case 'changeDesc':
        $script = isset($_POST['script']) ? urldecode(($_POST['script'])) : "";
        $newDesc = isset($_POST['newDesc']) ? urldecode(($_POST['newDesc'])) : "";
        file_put_contents("$compose_root/$script/description",trim($newDesc));
        break;
    case 'getYml':
        $script = isset($_POST['script']) ? urldecode(($_POST['script'])) : "";
        $scriptContents = file_get_contents("$compose_root/$script/compose.yml");
        $scriptContents = str_replace("\r","",$scriptContents);
        echo $scriptContents;
        if ( ! $scriptContents ) {
            echo "services:\n";
        }
        break;
    case 'saveYml':
        $script = isset($_POST['script']) ? urldecode(($_POST['script'])) : "";
        $scriptContents = isset($_POST['scriptContents']) ? $_POST['scriptContents'] : "";
    //		$scriptContents = preg_replace('/[\x80-\xFF]/', '', $scriptContents);
        file_put_contents("$compose_root/$script/compose.yml",$scriptContents);
        echo "$compose_root/$script/compose.yml saved";
        break;
    case 'getEnv':
        $script = isset($_POST['script']) ? urldecode(($_POST['script'])) : "";
        $scriptContents = is_file("$compose_root/$script/.env") ? file_get_contents("$compose_root/$script/.env") : "";
        $scriptContents = str_replace("\r","",$scriptContents);
        echo $scriptContents;
        if ( ! $scriptContents ) {
            echo "\n";
        }
        break;
    case 'saveEnv':
        $script = isset($_POST['script']) ? urldecode(($_POST['script'])) : "";
        $scriptContents = isset($_POST['scriptContents']) ? $_POST['scriptContents'] : "";
    //		$scriptContents = preg_replace('/[\x80-\xFF]/', '', $scriptContents);
        file_put_contents("$compose_root/$script/.env",$scriptContents);
        echo "$compose_root/$script/.env saved";
        break;
    case 'updateAutostart':
        $script = isset($_POST['script']) ? urldecode(($_POST['script'])) : "";
        if ( ! $script ) {
            echo "huh?";
            break;
        }
        $autostart = isset($_POST['autostart']) ? urldecode(($_POST['autostart'])) : "false";
        $fileName = "$compose_root/$script/autostart";
        if ( is_file($fileName) ) {
            exec("rm ".escapeshellarg($fileName));
        }
        file_put_contents($fileName,$autostart);
        break;
}

?>