<?php

use Oct8pus\CSV\CSV;
use Oct8pus\CSV\CSVException;

require_once './vendor/autoload.php';

// command line error handler
(new \NunoMaduro\Collision\Provider())->register();

$dir = __DIR__ . DIRECTORY_SEPARATOR . 'samples' . DIRECTORY_SEPARATOR;

$files = scandir($dir);

$csv = new CSV();

foreach ($files as $file) {
    if (!str_ends_with($file, '.csv')) {
        continue;
    }

    echo $csv
        ->setFile($dir . $file)
        ->autoDetect() . PHP_EOL;
}
