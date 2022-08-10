# nano csv

Parse csv files

[![Latest Stable Version](http://poser.pugx.org/8ctopus/nano-csv/v)](https://packagist.org/packages/8ctopus/nano-csv) [![Total Downloads](http://poser.pugx.org/8ctopus/nano-csv/downloads)](https://packagist.org/packages/8ctopus/nano-csv) [![License](http://poser.pugx.org/8ctopus/nano-csv/license)](https://packagist.org/packages/8ctopus/nano-csv) [![PHP Version Require](http://poser.pugx.org/8ctopus/nano-csv/require/php)](https://packagist.org/packages/8ctopus/nano-csv)

## features

- auto detect file encoding and line endings
- auto detect csv separator, enclosure, header
- low memory footprint
- unicode support

## install and demo

```sh
composer require 8ctopus/nano-csv
```

```php
use Oct8pus\CSV\CSV;

require_once './vendor/autoload.php';

$csv = new CSV(__DIR__ .'/samples/ascii-mac-header.csv');

echo $csv
    ->autoDetect() . PHP_EOL;

while ($row = $csv->readNextRow()) {
    echo implode(', ', $row) . PHP_EOL;
}
```

```txt
file: /dev/github/nano-csv/samples/ascii-mac-header.csv
size: 500
BOM: None
encoding: ASCII
line ending: Mac
lines count: 9
separator: ,
enclosure: "
header: true
rows count: 8
columns (13): Month, Average, 2005, 2006, 2007, 2008, 2009, 2010, 2011, 2012, 2013, 2014, 2015

May, 0.1, 0, 0, 1, 1, 0, 0, 0, 2, 0, 0, 0
Jun, 0.5, 2, 1, 1, 0, 0, 1, 1, 2, 2, 0, 1
Jul, 0.7, 5, 1, 1, 2, 0, 1, 3, 0, 2, 2, 1
Aug, 2.3, 6, 3, 2, 4, 4, 4, 7, 8, 2, 2, 3
Sep, 3.5, 6, 4, 7, 4, 2, 8, 5, 2, 5, 2, 5
Oct, 2.0, 8, 0, 1, 3, 2, 5, 1, 5, 2, 3, 0
Nov, 0.5, 3, 0, 0, 1, 1, 0, 1, 0, 1, 0, 1
Dec, 0.0, 1, 0, 1, 0, 0, 0, 0, 0, 0, 0, 1
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

https://filesamples.com/formats/csv \
https://eforexcel.com/wp/downloads-18-sample-csv-files-data-sets-for-testing-sales/
