<?php

require_once("/usr/local/emhttp/plugins/compose.manager/php/defines.php");

switch ($_POST['action']) {
	case 'composeUp':
		$path = isset($_POST['path']) ? urldecode(($_POST['path'])) : "";
		$unRaidVars = parse_ini_file("/var/local/emhttp/var.ini");
		if ($unRaidVars['mdState'] != "STARTED" ) {
			echo $plugin_root."/scripts/arrayNotStarted.sh";
			logger("Array not Started!");
			break;
		}
		$path .= "/compose.yml";
		// exec("chmod +x ".escapeshellarg($plugin_root."/scripts/compose.sh"));
		$composeCommand = $plugin_root."/scripts/compose.sh"."&arg1=".$path."&arg2=up";
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
		$path .= "/compose.yml";
		// exec("chmod +x ".escapeshellarg($plugin_root."/scripts/compose.sh"));
		$composeCommand = $plugin_root."/scripts/compose.sh"."&arg1=".$path."&arg2=down";
		echo $composeCommand;
		break;
}
?>