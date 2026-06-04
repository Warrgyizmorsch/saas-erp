<?php

$file = 'storage/logs/laravel.log';
if (!file_exists($file)) {
    echo "Log file does not exist.\n";
    exit;
}

$lines = file($file);
$lastLines = array_slice($lines, -150);
foreach ($lastLines as $line) {
    // skip base64 looking long strings
    if (strlen($line) > 500) {
        continue;
    }
    echo $line;
}
