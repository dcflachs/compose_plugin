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
        $folderName = preg_replace("/\s/", "_", $folderName);
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
        $fileName = "docker-compose.yml";

        $scriptContents = file_get_contents("$basePath/$fileName");
        $scriptContents = str_replace("\r","",$scriptContents);
        if ( ! $scriptContents ) {
            $scriptContents = "services:\n";
        }
        echo json_encode( [ 'result' => 'success', 'fileName' => "$basePath/$fileName", 'content' => $scriptContents ] );
        break;
    case 'getEnv':
        $script = isset($_POST['script']) ? urldecode(($_POST['script'])) : "";
        $basePath = getPath("$compose_root/$script");
        $fileName = "$basePath/.env";
        if ( is_file("$basePath/envpath") ) {
            $fileName = file_get_contents("$basePath/envpath");
            $fileName = str_replace("\r","",$fileName);
        }

        $scriptContents = is_file("$fileName") ? file_get_contents("$fileName") : "";
        $scriptContents = str_replace("\r","",$scriptContents);
        if ( ! $scriptContents ) {
            $scriptContents = "\n";
        }
        echo json_encode( [ 'result' => 'success', 'fileName' => "$fileName", 'content' => $scriptContents ] );
        break;
    case 'getOverride':
        $script = isset($_POST['script']) ? urldecode(($_POST['script'])) : "";
        $basePath = "$compose_root/$script";
        $fileName = "docker-compose.override.yml";

        $scriptContents = is_file("$basePath/$fileName") ? file_get_contents("$basePath/$fileName") : "";
        $scriptContents = str_replace("\r","",$scriptContents);
        if ( ! $scriptContents ) {
            $scriptContents = "";
        }
        echo json_encode( [ 'result' => 'success', 'fileName' => "$basePath/$fileName", 'content' => $scriptContents ] );
        break;
    case 'saveYml':
        $script = isset($_POST['script']) ? urldecode(($_POST['script'])) : "";
        $scriptContents = isset($_POST['scriptContents']) ? $_POST['scriptContents'] : "";
        $basePath = getPath("$compose_root/$script");
        $fileName = "docker-compose.yml";
    
        file_put_contents("$basePath/$fileName",$scriptContents);
        echo "$basePath/$fileName saved";
        break;
    case 'saveEnv':
        $script = isset($_POST['script']) ? urldecode(($_POST['script'])) : "";
        $scriptContents = isset($_POST['scriptContents']) ? $_POST['scriptContents'] : "";
        $basePath = getPath("$compose_root/$script");
        $fileName = "$basePath/.env";
        if ( is_file("$basePath/envpath") ) {
            $fileName = file_get_contents("$basePath/envpath");
            $fileName = str_replace("\r","",$fileName);
        }

        file_put_contents("$fileName",$scriptContents);
        echo "$fileName saved";
        break;
    case 'saveOverride':
        $script = isset($_POST['script']) ? urldecode(($_POST['script'])) : "";
        $scriptContents = isset($_POST['scriptContents']) ? $_POST['scriptContents'] : "";
        $basePath = "$compose_root/$script";
        $fileName = "docker-compose.override.yml";

        file_put_contents("$basePath/$fileName",$scriptContents);
        echo "$basePath/$fileName saved";
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
    case 'patchUI':
        exec("$plugin_root/scripts/patch_ui.sh");
        break;
    case 'unPatchUI':
        exec("$plugin_root/scripts/patch_ui.sh -r");
        break;
    case 'setEnvPath':
        $script = isset($_POST['script']) ? urldecode(($_POST['script'])) : "";
        if ( ! $script ) {
            echo json_encode( [ 'result' => 'error', 'message' => 'Stack not specified.' ] );
            break;
        }
        $fileContent = isset($_POST['envPath']) ? urldecode(($_POST['envPath'])) : "";
        $fileName = "$compose_root/$script/envpath";
        if ( is_file($fileName) ) {
            exec("rm ".escapeshellarg($fileName));
        }
        if ( isset($fileContent) && !empty($fileContent) ) {
            file_put_contents($fileName,$fileContent);
        }
        echo json_encode( [ 'result' => 'success', 'message' => '' ] );
        break;
    case 'getEnvPath':
        $script = isset($_POST['script']) ? urldecode(($_POST['script'])) : "";
        if ( ! $script ) {
            echo json_encode( [ 'result' => 'error', 'message' => 'Stack not specified.' ] );
            break;
        }
        $fileName = "$compose_root/$script/envpath";
        $fileContents = is_file("$fileName") ? file_get_contents("$fileName") : "";
        $fileContents = str_replace("\r","",$fileContents);
        if ( ! $fileContents ) {
            $fileContents = "";
        }
        echo json_encode( [ 'result' => 'success', 'fileName' => "$fileName", 'content' => $fileContents ] );
        break;
    case 'saveProfiles':
        $script = isset($_POST['script']) ? urldecode(($_POST['script'])) : "";
        $scriptContents = isset($_POST['scriptContents']) ? $_POST['scriptContents'] : "";
        $basePath = "$compose_root/$script";
        $fileName = "$basePath/profiles";

        if( $scriptContents == "[]" ) {
            if ( is_file($fileName) ) {
                exec("rm ".escapeshellarg($fileName));
                echo json_encode( [ 'result' => 'success', 'message' => "$fileName deleted" ] );
            }

            echo json_encode( [ 'result' => 'success', 'message' => '' ] );
            break;
        }

        file_put_contents("$fileName",$scriptContents);
        echo json_encode( [ 'result' => 'success', 'message' => "$fileName saved" ] );
        break;
}

?>