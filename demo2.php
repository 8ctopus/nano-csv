<?php declare(strict_types=1);

use Oct8pus\CSV\CSV;

require_once './vendor/autoload.php';

// command line error handler
(new \NunoMaduro\Collision\Provider())->register();

$csv = new CSV(__DIR__ . '/samples/ascii-mac-header.csv');

$csv
    ->autoDetect()
    // convert string to number
    ->setConvertNumbers(true)
    // return associative array
    ->setAssociativeArray(true);

$average = 0;

while ($row = $csv->readNextRow()) {
    $average += $row['Average'];
}

echo "Average from May to Dec: {$average}" . PHP_EOL;
