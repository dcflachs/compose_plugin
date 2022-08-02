<?php

function sanitizeStr($a) {
	$a = str_replace(".","_",$a);
	$a = str_replace(" ","_",$a);
	$a = str_replace("-","_",$a);
    return strtolower($a);
}

function isIndirect($path) {
    return is_file("$path/indirect");
}

function getPath($basePath) {
    $outPath = $basePath;
    if ( isIndirect($basePath) ) {
        $outPath = file_get_contents("$basePath/indirect");
    }

    return $outPath;
}

?>