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

$csv = new CSV()
echo $csv
    ->setFile(__DIR__ .'/samples/utf16le-windows.csv')
    ->autoDetect();
```

```txt
file: K:\dev\github\nano-csv\samples\utf16le-windows.csv
size: 115852
BOM: UTF-16LE
encoding: UTF-16LE
line ending: Windows
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