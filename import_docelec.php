<?php

$dir = new RecursiveDirectoryIterator(realpath('../docelec'));


foreach (new RecursiveIteratorIterator($dir) as $filename=>$cur) {
    if (preg_match('/kbart/i', $filename)) {
        $pathinfo = pathinfo($filename, PATHINFO_DIRNAME);
        $pathinfo = array_filter( explode('/', $pathinfo) );
        $parentResource = array_pop($pathinfo);
        $command = 'php import.php --file="' . $filename . '" --parent="' . $parentResource . '"';
        echo "\n\n$command\n";
        system($command); 
    }
}


foreach (new RecursiveIteratorIterator($dir) as $filename=>$cur) {
    if (preg_match('/note/i', $filename)) {
        $command = 'php import_notes.php "' . $filename . '"';
        echo "\n\n$command\n";
        $output = exec($command); 
        print_r($output);
    }
}

foreach (new RecursiveIteratorIterator($dir) as $filename=>$cur) {
    if (preg_match('/finance/i', $filename)) {
        $command = 'php import_funds.php "' . $filename . '"';
        echo "\n\n$command\n";
        $output = exec($command); 
        print_r($output);
    }
}



?>
