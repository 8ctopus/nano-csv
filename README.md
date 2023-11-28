# nano csv

[![packagist](http://poser.pugx.org/8ctopus/nano-csv/v)](https://packagist.org/packages/8ctopus/nano-csv)
[![downloads](http://poser.pugx.org/8ctopus/nano-csv/downloads)](https://packagist.org/packages/8ctopus/nano-csv)
[![min php version](http://poser.pugx.org/8ctopus/nano-csv/require/php)](https://packagist.org/packages/8ctopus/nano-csv)
[![license](http://poser.pugx.org/8ctopus/nano-csv/license)](https://packagist.org/packages/8ctopus/nano-csv)
[![tests](https://github.com/8ctopus/nano-csv/actions/workflows/tests.yml/badge.svg)](https://github.com/8ctopus/nano-csv/actions/workflows/tests.yml)
![code coverage badge](https://raw.githubusercontent.com/8ctopus/nano-csv/image-data/coverage.svg)
![lines of code](https://raw.githubusercontent.com/8ctopus/nano-csv/image-data/lines.svg)

Parse csv and Excel xlsx files


## features

- parse csv and Excel xlsx files
- no dependencies, fast and low memory footprint
- very small code base: 1100 lines of code
- auto detect file encoding and line endings
- auto detect csv separator, enclosure and header presence
- unicode support

## install and demo

    composer require 8ctopus/nano-csv

- Simple csv parsing

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

- And some handy options

```php
$csv = new CSV(__DIR__ .'/samples/ascii-mac-header.csv');

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
```

```txt
Average from May to Dec: 9.6
```

- Excel xlsx parsing

```php
use Oct8pus\CSV\XLSX;

$xls = new XLSX(__DIR__ .'/samples/test.xlsx');

echo $xls
    ->autoDetect() . PHP_EOL;

while ($row = $xls->readNextRow()) {
    echo implode(', ', $row) . PHP_EOL;
}
```

```txt
file: K:\dev\github\nano-csv/samples\test.csv
size: 174
BOM: UTF-8
encoding: UTF-8
line ending: Linux
lines count: 9
separator: ,
enclosure: none
header: true
rows count: 7
columns (5): name, class, weight, empty, height

cat, mammal, 8, , 0.2
rabbit, mammal, 0.6, , 0.2
dog, mammal, 20, , 0.7
puma, mammal, 30, , 0.6
pinguin, bird, 10, , 0.4
bear, mammal, 300, , 1
bat, mammal, 0.1, , 0.1
```

Also look at the `demo-*` files.

## tests

    composer test

## clean code

    composer fix
    composer fix-risky

## todo

- use readonly properties
- detect escape char
- refactor read
- make a really tricky test file - detect escape character within enclosures
- compare performance against most popular csv parsers

## credits

    https://filesamples.com/formats/csv
    https://eforexcel.com/wp/downloads-18-sample-csv-files-data-sets-for-testing-sales/
