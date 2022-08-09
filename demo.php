<?php

use Oct8pus\CSV\CSV;
use Oct8pus\CSV\CSVException;
use Oct8pus\CSV\File;
use Oct8pus\CSV\FileException;

require_once './vendor/autoload.php';

// command line error handler
(new \NunoMaduro\Collision\Provider())->register();

$dir = __DIR__ . DIRECTORY_SEPARATOR . 'samples' . DIRECTORY_SEPARATOR;

$files = scandir($dir);

$files = [
//    'ascii-linux-header.csv',
    'utf16be-windows-header.csv',
//    'ascii-mac-header.csv',
//    'utf16le-windows-header.csv',
];

foreach ($files as $file) {
    if (!str_ends_with($file, '.csv')) {
        continue;
    }

/*
    $csv = new CSV($dir . $file);
    echo $csv
        ->autoDetect();
*/
/*
    $i = 0;

    while (($row = $csv->readNextRow()) && ++$i < 6) {
        echo implode(', ', $row) . PHP_EOL;
    }
*/
/*
    $row = $csv->readRow(0);

    echo implode(', ', $row) . PHP_EOL;

    echo PHP_EOL;
*/

    $file = new File($dir . $file);
    echo $file->autoDetect() . PHP_EOL;

    echo $file->readLine(0) . PHP_EOL;
    echo $file->readLine(1) . PHP_EOL;
    echo $file->readLine(2) . PHP_EOL . PHP_EOL;

    $i = 0;

    while (($line = $file->readNextLine()) && ++$i <= 6) {
        echo $line . PHP_EOL;
    }

    echo PHP_EOL;
}
