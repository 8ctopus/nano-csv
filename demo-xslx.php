<?php

use Oct8pus\CSV\XLS;

require_once './vendor/autoload.php';

// command line error handler
(new \NunoMaduro\Collision\Provider())->register();

$xls = new XLS(__DIR__ .'/samples/test.xlsx');

echo $xls
    ->autoDetect() . PHP_EOL;

while ($row = $xls->readNextRow()) {
    echo implode(', ', $row) . PHP_EOL;
}
