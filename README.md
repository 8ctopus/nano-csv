# nano csv

Parse csv files

[![Latest Stable Version](http://poser.pugx.org/8ctopus/nano-csv/v)](https://packagist.org/packages/8ctopus/nano-csv) [![Total Downloads](http://poser.pugx.org/8ctopus/nano-csv/downloads)](https://packagist.org/packages/8ctopus/nano-csv) [![License](http://poser.pugx.org/8ctopus/nano-csv/license)](https://packagist.org/packages/8ctopus/nano-csv) [![PHP Version Require](http://poser.pugx.org/8ctopus/nano-csv/require/php)](https://packagist.org/packages/8ctopus/nano-csv)

## install and demo

```sh
composer require 8ctopus/nano-csv
```

```php
use Oct8pus\CSV\CSV;

require_once './vendor/autoload.php';

$csv = new CSV(__DIR__ .'/samples/utf16le-windows-header.csv');

echo $csv
    ->autoDetect() . PHP_EOL;

while ($row = $csv->readNextRow()) {
    echo implode(', ', $row) . PHP_EOL;
}
```

```txt
file: K:\dev\github\nano-csv/samples/utf16le-windows-header.csv
size: 115850
BOM: UTF-16LE
encoding: UTF-16LE
line ending: Windows
separator: ,
enclosure: "
header: true
columns (6): Name, Team, Position, Height(inches), Weight(lbs), Age

Adam Donachie, BAL, Catcher, 74, 180, 22.99
Paul Bako, BAL, Catcher, 74, 215, 34.69
...
```

## tests

```sh
vendor/bin/phpunit --coverage-html coverage
```

## clean code

```sh
vendor/bin/php-cs-fixer fix
```

## credits

https://filesamples.com/formats/csv
https://eforexcel.com/wp/downloads-18-sample-csv-files-data-sets-for-testing-sales/
