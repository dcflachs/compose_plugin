<?php

require_once("/usr/local/emhttp/plugins/compose.manager/php/defines.php");

function sanitizeStr($a) {
	$a = str_replace(".","_",$a);
	$a = str_replace(" ","_",$a);
	return str_replace("-","_",$a);
}

switch ($_POST['action']) {
	case 'composeUp':
		$path = isset($_POST['path']) ? urldecode(($_POST['path'])) : "";
		$unRaidVars = parse_ini_file("/var/local/emhttp/var.ini");
		if ($unRaidVars['mdState'] != "STARTED" ) {
			echo $plugin_root."/scripts/arrayNotStarted.sh";
			logger("Array not Started!");
			break;
		}
		$projectName = basename($path);
		if ( is_file("$path/name") ) {
			$projectName = trim(file_get_contents("$path/name"));
		}
		$projectName = sanitizeStr($projectName);
		$path .= "/compose.yml";
		// exec("chmod +x ".escapeshellarg($plugin_root."/scripts/compose.sh"));
		$composeCommand = $plugin_root."/scripts/compose.sh"."&arg1=up"."&arg2=".$path."&arg3=".$projectName;
		echo $composeCommand;
		break;
	case 'composeDown':
		$path = isset($_POST['path']) ? urldecode(($_POST['path'])) : "";
		$unRaidVars = parse_ini_file("/var/local/emhttp/var.ini");
		if ($unRaidVars['mdState'] != "STARTED" ) {
			echo $plugin_root."/scripts/arrayNotStarted.sh";
			logger("Array not Started!");
			break;
		}
		$projectName = basename($path);
		if ( is_file("$path/name") ) {
			$projectName = trim(file_get_contents("$path/name"));
		}
		$projectName = sanitizeStr($projectName);
		$path .= "/compose.yml";
		// exec("chmod +x ".escapeshellarg($plugin_root."/scripts/compose.sh"));
		$composeCommand = $plugin_root."/scripts/compose.sh"."&arg1=down"."&arg2=".$path."&arg3=".$projectName;
		echo $composeCommand;
		break;
	case 'composePull':
			$path = isset($_POST['path']) ? urldecode(($_POST['path'])) : "";
			$unRaidVars = parse_ini_file("/var/local/emhttp/var.ini");
			if ($unRaidVars['mdState'] != "STARTED" ) {
				echo $plugin_root."/scripts/arrayNotStarted.sh";
				logger("Array not Started!");
				break;
			}
			$projectName = basename($path);
			if ( is_file("$path/name") ) {
				$projectName = trim(file_get_contents("$path/name"));
			}
			$projectName = sanitizeStr($projectName);
			$path .= "/compose.yml";
			// exec("chmod +x ".escapeshellarg($plugin_root."/scripts/compose.sh"));
			$composeCommand = $plugin_root."/scripts/compose.sh"."&arg1=pull"."&arg2=".$path."&arg3=".$projectName;
			echo $composeCommand;
			break;
}
?>