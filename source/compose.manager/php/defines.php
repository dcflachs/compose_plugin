<?php
require_once("/usr/local/emhttp/plugins/dynamix/include/Wrappers.php");

function locate_compose_root($name) {
    $cfg = parse_plugin_cfg($name);
    return $cfg['PROJECTS_FOLDER'] ?? "/boot/config/plugins/compose.manager/projects";
}

$plugin_root = "/usr/local/emhttp/plugins/compose.manager/";
$socket_name = "compose_manager_action";
$sName = "compose.manager";
$docker_label_managed = "net.unraid.docker.managed";
$docker_label_icon = "net.unraid.docker.icon";
$docker_label_webui = "net.unraid.docker.webui";
$docker_label_shell = "net.unraid.docker.shell";
$docker_label_managed_name = "composeman";
$compose_root = locate_compose_root($sName);
?>