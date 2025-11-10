<?php

require_once("/usr/local/emhttp/plugins/compose.manager/php/defines.php");
require_once("/usr/local/emhttp/plugins/compose.manager/php/util.php");
require_once("/usr/local/emhttp/plugins/dynamix/include/Wrappers.php");

function logger($string) {
	$string = escapeshellarg($string);
	exec("logger ".$string);
}

function execComposeCommandInTTY($cmd, $debug)
{
	global $socket_name;
	$pid = exec("pgrep -a ttyd|awk '/\\/$socket_name\\.sock/{print \$1}'");
	if ( $debug ) {
		logger($pid);
	}
	if ($pid) exec("kill $pid");
	@unlink("/var/tmp/$socket_name.sock");
	$command = "ttyd -R -o -i '/var/tmp/$socket_name.sock' $cmd". " > /dev/null &"; 
	exec($command);
	if ( $debug ) {
		logger($command);
	}
}

function echoComposeCommand($action)
{
	global $plugin_root;
	global $sName;
	$cfg = parse_plugin_cfg($sName);
	$debug = $cfg['DEBUG_TO_LOG'] == "true";
	$path = isset($_POST['path']) ? urldecode(($_POST['path'])) : "";
	$profile = isset($_POST['profile']) ? urldecode(($_POST['profile'])) : "";
	$unRaidVars = parse_ini_file("/var/local/emhttp/var.ini");
	if ($unRaidVars['mdState'] != "STARTED" ) {
		echo $plugin_root."/scripts/arrayNotStarted.sh";
		if ( $debug ) {
			logger("Array not Started!");
		}
	}
	else
	{
		$composeCommand = array($plugin_root."scripts/compose.sh");

		$projectName = basename($path);
		if ( is_file("$path/name") ) {
			$projectName = trim(file_get_contents("$path/name"));
		}
		$projectName = sanitizeStr($projectName);

		$projectName = "-p$projectName";
		$action = "-c$action";
		$composeCommand[] = $action;
		$composeCommand[] = $projectName;

		$composeFile = "";
		if( isIndirect($path) ) {
			$composeFile = getPath($path);
			$composeFile = "-d$composeFile";
		} 
		else {
			$foundComposeFile = findComposeFile($path);
			if ($foundComposeFile === null) {
				$composeFile .= "$path/docker-compose.yml";
			} else {
				$composeFile .= $foundComposeFile;
			}
			$composeFile = "-f$composeFile";
		}
		$composeCommand[] = $composeFile;

		// First, always include the plugin's override file if it exists
		global $compose_root;
		$projectName = basename($path);
		$pluginOverrideYml = "$compose_root/$projectName/docker-compose.override.yml";
		$pluginOverrideYaml = "$compose_root/$projectName/docker-compose.override.yaml";
		
		if (is_file($pluginOverrideYml)) {
			$composeOverride = "-f$pluginOverrideYml";
			$composeCommand[] = $composeOverride;
			if ( $debug ) {
				logger("Using plugin override file: $pluginOverrideYml");
			}
		} else if (is_file($pluginOverrideYaml)) {
			$composeOverride = "-f$pluginOverrideYaml";
			$composeCommand[] = $composeOverride;
			if ( $debug ) {
				logger("Using plugin override file: $pluginOverrideYaml");
			}
		}

		// Then, also include any project-specific override files
		if (isIndirect($path)) {
			$basePath = getPath($path);
		} else {
			$basePath = $path;
		}
		
		$foundComposeFile = findComposeFile($basePath);
		if ($foundComposeFile !== null) {
			$baseFileName = getComposeFileBaseName($foundComposeFile);
			// Get the extension of the original compose file
			$extension = pathinfo($foundComposeFile, PATHINFO_EXTENSION);
			$overrideFile = "$basePath/$baseFileName.override.$extension";
			if (is_file($overrideFile)) {
				$composeOverride = "-f$overrideFile";
				$composeCommand[] = $composeOverride;
				if ( $debug ) {
					logger("Using project override file: $overrideFile");
				}
			}
		} else {
			// Check for both yml and yaml override files
			if (is_file("$basePath/docker-compose.override.yml")) {
				$composeOverride = "-f$basePath/docker-compose.override.yml";
				$composeCommand[] = $composeOverride;
				if ( $debug ) {
					logger("Using project override file: $basePath/docker-compose.override.yml");
				}
			} else if (is_file("$basePath/docker-compose.override.yaml")) {
				$composeOverride = "-f$basePath/docker-compose.override.yaml";
				$composeCommand[] = $composeOverride;
				if ( $debug ) {
					logger("Using project override file: $basePath/docker-compose.override.yaml");
				}
			}
		}

		if ( is_file("$path/envpath") ) {
			$envPath = "-e" . trim(file_get_contents("$path/envpath"));
			$composeCommand[] = $envPath;
		}

		if( $profile ) {
			$profile = "-g $profile";
			$composeCommand[] = $profile;
		}

		if( $debug ) {
			$composeCommand[] = "--debug";
		}

		if ($cfg['OUTPUTSTYLE'] == "ttyd") {
			$composeCommand = array_map(function($item) {
				return escapeshellarg($item);
			}, $composeCommand);
			$composeCommand = join(" ", $composeCommand);
			execComposeCommandInTTY($composeCommand, $debug);
			if ( $debug ) {
				logger($composeCommand);
			}
			$composeCommand = "/plugins/compose.manager/php/show_ttyd.php";
		}
		else {
			$i = 0;
			$composeCommand = array_reduce($composeCommand, function($v1, $v2) use (&$i) {
				if ($v2[0] == "-") {
					$i++; // increment $i
					return $v1."&arg".$i."=".$v2;
				}
				else{
					return $v1.$v2;
				}
			}, "");
		}
		
		echo $composeCommand;
		if ( $debug ) {
			logger($composeCommand);
		}
	}	
}

switch ($_POST['action']) {
	case 'composeUp':
		echoComposeCommand('up');
		break;
	case 'composeDown':
		echoComposeCommand('down');
		break;
	case 'composeUpPullBuild':
		echoComposeCommand('update');
		break;
	case 'composeStop':
		echoComposeCommand('stop');
		break;
	case 'composeLogs':
		echoComposeCommand('logs');
		break;		
}
?>