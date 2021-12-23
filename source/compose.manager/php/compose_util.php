<?php

require_once("/usr/local/emhttp/plugins/compose.manager/php/defines.php");

function sanitizeStr($a) {
	$a = str_replace(".","_",$a);
	$a = str_replace(" ","_",$a);
	return str_replace("-","_",$a);
}

function echoComposeCommand($action)
{
	global $plugin_root;
	$path = isset($_POST['path']) ? urldecode(($_POST['path'])) : "";
	$unRaidVars = parse_ini_file("/var/local/emhttp/var.ini");
	if ($unRaidVars['mdState'] != "STARTED" ) {
		echo $plugin_root."/scripts/arrayNotStarted.sh";
		logger("Array not Started!");
	}
	else
	{
		$projectName = basename($path);
		if ( is_file("$path/name") ) {
			$projectName = trim(file_get_contents("$path/name"));
		}
		$projectName = sanitizeStr($projectName);
		$path .= "/compose.yml";
		// exec("chmod +x ".escapeshellarg($plugin_root."/scripts/compose.sh"));
		$composeCommand = $plugin_root."/scripts/compose.sh"."&arg1=".$action."&arg2=".$path."&arg3=".$projectName;
		echo $composeCommand;
	}
}

switch ($_POST['action']) {
	case 'composeUp':
		echoComposeCommand('up');
		break;
	case 'composeDown':
		echoComposeCommand('down');
		break;
	case 'composePull':
		echoComposeCommand('pull');
		break;
}
?>