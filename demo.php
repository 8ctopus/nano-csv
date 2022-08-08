<?php

use Oct8pus\CSV\CSV;
use Oct8pus\CSV\CSVException;

require_once './vendor/autoload.php';

// command line error handler
(new \NunoMaduro\Collision\Provider())->register();

$dir = __DIR__ . DIRECTORY_SEPARATOR . 'samples' . DIRECTORY_SEPARATOR;

$files = scandir($dir);

$files = [
    'utf16be-windows-header.csv',
];

foreach ($files as $file) {
    if (!str_ends_with($file, '.csv')) {
        continue;
    }

    $csv = new CSV($dir . $file);
    echo $csv
        ->autoDetect() . PHP_EOL;

    var_dump($csv->readRow(1));
}
