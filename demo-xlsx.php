<?php declare(strict_types=1);

use Oct8pus\CSV\XLSX;

require_once './vendor/autoload.php';

// command line error handler
(new \NunoMaduro\Collision\Provider())->register();

$xlsx = new XLSX(__DIR__ . '/samples/test.xlsx');

echo $xlsx
    ->autoDetect() . PHP_EOL;

while ($row = $xlsx->readNextRow()) {
    echo implode(', ', $row) . PHP_EOL;
}
