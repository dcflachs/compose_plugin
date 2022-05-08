<?php

function sanitizeStr($a) {
	$a = str_replace(".","_",$a);
	$a = str_replace(" ","_",$a);
	return str_replace("-","_",$a);
}

?>