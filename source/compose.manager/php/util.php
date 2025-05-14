<?php

/**
 * Find the first valid compose file in a directory following Docker Compose priority order
 *
 * @param string $dir Directory to search in
 * @return string|null Path to the first valid compose file found, or null if none found
 */
function findComposeFile($dir) {
    // Docker Compose priority order: compose.yaml, compose.yml, docker-compose.yaml, docker-compose.yml
    $possibleFiles = [
        "$dir/compose.yaml",
        "$dir/compose.yml",
        "$dir/docker-compose.yaml",
        "$dir/docker-compose.yml"
    ];
    
    foreach ($possibleFiles as $file) {
        if (file_exists($file)) {
            return $file;
        }
    }
    
    return null;
}

/**
 * Get the base name of a compose file without extension
 *
 * @param string $composeFilePath Full path to compose file
 * @return string Base name (e.g., "compose" or "docker-compose")
 */
function getComposeFileBaseName($composeFilePath) {
    $filename = basename($composeFilePath);
    return pathinfo($filename, PATHINFO_FILENAME);
}

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