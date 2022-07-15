<?php

require_once("/usr/local/emhttp/plugins/compose.manager/php/defines.php");
require_once("/usr/local/emhttp/plugins/compose.manager/php/util.php");

function getElement($element) {
    $return = str_replace(".","-",$element);
    $return = str_replace(" ","",$return);
    return $return;
}

switch ($_POST['action']) {
    case 'addStack':
        #Create indirect
        $indirect = isset($_POST['stackPath']) ? urldecode(($_POST['stackPath'])) : "";
        if ( !empty($indirect) ) {
            if ( !is_dir($indirect) ) {
                exec("mkdir -p ".escapeshellarg($indirect));
                if( !is_dir($indirect)  ) {
                    echo json_encode( [ 'result' => 'error', 'message' => 'Failed to create stack directory.' ] );
                    break;
                }
            }
        }

        #Pull stack files

        #Create stack folder
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
        if( !is_dir($folder)  ) {
            echo json_encode( [ 'result' => 'error', 'message' => 'Failed to create stack directory.' ] );
            break;
        }

        #Create stack files
        if ( !empty($indirect) ) {
            file_put_contents("$folder/indirect",$indirect);
            if ( !is_file("$indirect/docker-compose.yml") ) {
                file_put_contents("$indirect/docker-compose.yml","services:\n");
            }
        } else {
            file_put_contents("$folder/docker-compose.yml","services:\n");
        }

        file_put_contents("$folder/name",$stackName);

        echo json_encode( [ 'result' => 'success', 'message' => '' ] );
        break;
    case 'deleteStack':
        $stackName = isset($_POST['stackName']) ? urldecode(($_POST['stackName'])) : "";
        if ( ! $stackName ) {
            echo json_encode( [ 'result' => 'error', 'message' => 'Stack not specified.' ] );
          break;
        }
        $folderName = "$compose_root/$stackName";
        $filesRemain = is_file("$folderName/indirect") ? file_get_contents("$folderName/indirect") : "";
        exec("rm -rf ".escapeshellarg($folderName));
        if ( !empty($filesRemain) ){
            echo json_encode( [ 'result' => 'warning', 'message' => $filesRemain ] );
        } else {
            echo json_encode( [ 'result' => 'success', 'message' => '' ] );
        }
        break;
    case 'changeName':
        $script = isset($_POST['script']) ? urldecode(($_POST['script'])) : "";
        $newName = isset($_POST['newName']) ? urldecode(($_POST['newName'])) : "";
        file_put_contents("$compose_root/$script/name",trim($newName));
        echo json_encode( [ 'result' => 'success', 'message' => '' ] );
        break;
    case 'changeDesc':
        $script = isset($_POST['script']) ? urldecode(($_POST['script'])) : "";
        $newDesc = isset($_POST['newDesc']) ? urldecode(($_POST['newDesc'])) : "";
        file_put_contents("$compose_root/$script/description",trim($newDesc));
        echo json_encode( [ 'result' => 'success', 'message' => '' ] );
        break;
    case 'getYml':
        $script = isset($_POST['script']) ? urldecode(($_POST['script'])) : "";
        $basePath = getPath("$compose_root/$script");

        $scriptContents = file_get_contents("$basePath/docker-compose.yml");
        $scriptContents = str_replace("\r","",$scriptContents);
        if ( ! $scriptContents ) {
            $scriptContents = "services:\n";
        }
        echo json_encode( [ 'result' => 'success', 'fileName' => "$basePath/docker-compose.yml", 'content' => $scriptContents ] );
        break;
    case 'saveYml':
        $script = isset($_POST['script']) ? urldecode(($_POST['script'])) : "";
        $scriptContents = isset($_POST['scriptContents']) ? $_POST['scriptContents'] : "";
        $basePath = getPath("$compose_root/$script");

        file_put_contents("$basePath/docker-compose.yml",$scriptContents);
        echo "$basePath/docker-compose.yml saved";
        break;
    case 'getEnv':
        $script = isset($_POST['script']) ? urldecode(($_POST['script'])) : "";
        $basePath = getPath("$compose_root/$script");

        $scriptContents = is_file("$basePath/.env") ? file_get_contents("$basePath/.env") : "";
        $scriptContents = str_replace("\r","",$scriptContents);
        if ( ! $scriptContents ) {
            $scriptContents = "\n";
        }
        echo json_encode( [ 'result' => 'success', 'fileName' => "$basePath/.env", 'content' => $scriptContents ] );
        break;
    case 'saveEnv':
        $script = isset($_POST['script']) ? urldecode(($_POST['script'])) : "";
        $scriptContents = isset($_POST['scriptContents']) ? $_POST['scriptContents'] : "";
        $basePath = getPath("$compose_root/$script");

        file_put_contents("$basePath/.env",$scriptContents);
        echo "$basePath/.env saved";
        break;
    case 'updateAutostart':
        $script = isset($_POST['script']) ? urldecode(($_POST['script'])) : "";
        if ( ! $script ) {
            echo json_encode( [ 'result' => 'error', 'message' => 'Stack not specified.' ] );
            break;
        }
        $autostart = isset($_POST['autostart']) ? urldecode(($_POST['autostart'])) : "false";
        $fileName = "$compose_root/$script/autostart";
        if ( is_file($fileName) ) {
            exec("rm ".escapeshellarg($fileName));
        }
        file_put_contents($fileName,$autostart);
        echo json_encode( [ 'result' => 'success', 'message' => '' ] );
        break;
}

?>