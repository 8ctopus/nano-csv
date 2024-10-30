<?php

declare(strict_types=1);

use Oct8pus\CSV\CSV;

require_once __DIR__ . '/vendor/autoload.php';

// command line error handler
(new \NunoMaduro\Collision\Provider())
    ->register();

$csv = new CSV(__DIR__ . '/samples/ascii-mac-header.csv');

echo $csv
    ->autoDetect() . PHP_EOL;

while ($row = $csv->readNextRow()) {
    echo implode(', ', $row) . PHP_EOL;
}
