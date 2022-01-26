<?php

require_once("/usr/local/emhttp/plugins/compose.manager/php/defines.php");
require_once("/usr/local/emhttp/plugins/dynamix/include/Wrappers.php");

function logger($string) {
	$string = escapeshellarg($string);
	exec("logger ".$string);
}

function sanitizeStr($a) {
	$a = str_replace(".","_",$a);
	$a = str_replace(" ","_",$a);
	return str_replace("-","_",$a);
}

function execComposeCommandInTTY($cmd)
{
	global $socket_name;;
	$pid = exec("pgrep -a ttyd|awk '/\\/$socket_name\\.sock/{print \$1}'");
	logger($pid);
	if ($pid) exec("kill $pid");
	@unlink("/var/tmp/$socket_name.sock");
	$command = "ttyd -R -o -i '/var/tmp/$socket_name.sock' $cmd". " > /dev/null &"; 
	exec($command);
	logger($command);
}

function echoComposeCommand($action)
{
	global $plugin_root;
	global $sName;
	$cfg = parse_plugin_cfg($sName);
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

		if ($cfg['OUTPUTSTYLE'] == "ttyd") {
			$composeCommand = join(" ", array(escapeshellarg($plugin_root."scripts/compose.sh"),escapeshellarg($action),escapeshellarg($path),escapeshellarg($projectName)));
			execComposeCommandInTTY($composeCommand);
			logger($composeCommand);
			$composeCommand = "/plugins/compose.manager/php/show_ttyd.php";
		}
		else {
			$composeCommand = $plugin_root."/scripts/compose.sh"."&arg1=".$action."&arg2=".$path."&arg3=".$projectName;
		}
		
		echo $composeCommand;
		logger($composeCommand);
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