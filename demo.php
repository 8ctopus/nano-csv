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

/*
$files = [
//    'ascii-linux-header.csv',
    'utf16be-windows-header.csv',
//    'ascii-mac-header.csv',
//    'utf16le-windows-header.csv',
];
*/

foreach ($files as $file) {
    if (!str_ends_with($file, '.csv')) {
        continue;
    }

/*
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
*/

    $csv = new CSV($dir . $file);
    echo $csv
        ->autoDetect();

/*
    echo 'file: ' . $csv->getFile() . PHP_EOL;
    echo 'size: ' . $csv->getSize() . PHP_EOL;
    echo 'bom: ' . $csv->getBom()->toStr() . PHP_EOL;
    echo 'encoding: ' . $csv->getEncoding() . PHP_EOL;
    echo 'line ending: ' . $csv->getlineEnding()->toStr() . PHP_EOL;

    echo 'separator: ' . $csv->getSeparator() . PHP_EOL;
    echo 'enclosure: ' . $csv->getEnclosure() . PHP_EOL;
    //FIX ME echo 'escape: ' . $csv->getEscape() . PHP_EOL;
    echo 'columns (' . $csv->getColumnsCount() . '): ' . implode(', ', $csv->getColumns()) . PHP_EOL;

    $csv->setSeparator('|');
    $csv->setEnclosure('|');
    $csv->setEscape('\\');

    echo PHP_EOL;
    continue;
*/

    for ($i = 0; $i < 2; ++$i) {
        $row = $csv->readRow($i);
        echo implode(', ', $row) . PHP_EOL;
    }

    echo PHP_EOL;

    $i = 0;

    while (($row = $csv->readNextRow()) && ++$i < 6) {
        echo implode(', ', $row) . PHP_EOL;
    }
}
