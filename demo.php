<?php

use Oct8pus\CSV\CSV;
use Oct8pus\CSV\CSVException;

require_once './vendor/autoload.php';

// command line error handler
(new \NunoMaduro\Collision\Provider())->register();

$csv = new CSV();

$samples = [
    'sample1.csv',
    'sample2.csv',
    'sample3.csv',
    'sample4.csv',
];

foreach ($samples as $sample) {
    echo $csv
        ->setFile(__DIR__ ."/samples/{$sample}")
        ->autoDetect();
}
