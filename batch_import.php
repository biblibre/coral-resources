<?php

$longopts = array(
    "directory:",
);

$options = getopt("", $longopts);


if ($handle = opendir($options['directory'])) {
    while (false !== ($entry = readdir($handle))) {
        echo "$entry ";
        if (preg_match('/_Default_(.*)_[0-9]{4}-[0-9]{2}-[0-9]{2}/', $entry, $matches)) {
            $parent = str_replace('_', ' ', $matches[1]);
            echo " => " . $parent . "\n";
            $command = 'php import.php --file="' . $options['directory'] . '/' . $entry . '" --parent="' . $parent . '"';
            echo "$command\n";
            system($command);
        } else {
            echo "did not match\n";
        }
    }
}




?>
