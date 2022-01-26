<?php
require_once("/usr/local/emhttp/plugins/compose.manager/php/defines.php");
// $docroot = $docroot ?? $_SERVER['DOCUMENT_ROOT'] ?: '/usr/local/emhttp';
// require_once "$docroot/webGui/include/Secure.php";

// $done = unscript($_GET['done']??'');

$url = "/dockerterminal/$socket_name/";
echo '<iframe src="'.$url.'" style="border: none; width: 100%; height: 100%;"></iframe>';
// echo "<p class='centered'><button class='logLine' type='button' onclick=top.Shadowbox.close()'>"+decodeURI($done)+"</button></p>";
?>