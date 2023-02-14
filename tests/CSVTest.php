<?php

declare(strict_types=1);

namespace Oct8pus\CSV;

use PHPUnit\Framework\TestCase;

/**
 * @internal
 *
 * @covers \Oct8pus\CSV\CSV
 * @covers \Oct8pus\CSV\CSVException
 */
final class CSVTest extends TestCase
{
    /**
     * @dataProvider getAutoDetectCases
     *
     * @param string $file
     * @param string $expected
     */
    public function testAutoDetect(string $file, string $expected) : void
    {
        $csv = new CSV($file);
        $csv->autoDetect();

        //echo $csv;

        static::assertSame($expected, (string) $csv);
    }

    public function getAutoDetectCases() : array
    {
        return [
            [
                'file' => 'samples/ascii-linux-header.csv',
                'expected' => 'file: samples/ascii-linux-header.csv' . PHP_EOL .
                    'size: 723' . PHP_EOL .
                    'BOM: None' . PHP_EOL .
                    'encoding: ASCII' . PHP_EOL .
                    'line ending: Linux' . PHP_EOL .
                    'lines count: 102' . PHP_EOL .
                    'separator: ,' . PHP_EOL .
                    'enclosure: "' . PHP_EOL .
                    'header: true' . PHP_EOL .
                    'rows count: 100' . PHP_EOL .
                    'columns (2): Game Number, Game Length' . PHP_EOL,
            ],
            [
                'file' => 'samples/ascii-linux-no-header.csv',
                'expected' => 'file: samples/ascii-linux-no-header.csv' . PHP_EOL .
                    'size: 694' . PHP_EOL .
                    'BOM: None' . PHP_EOL .
                    'encoding: ASCII' . PHP_EOL .
                    'line ending: Linux' . PHP_EOL .
                    'lines count: 101' . PHP_EOL .
                    'separator: ,' . PHP_EOL .
                    'enclosure: none' . PHP_EOL .
                    'header: false' . PHP_EOL .
                    'rows count: 100' . PHP_EOL .
                    'columns (2): column 0, column 1' . PHP_EOL,
            ],
            [
                'file' => 'samples/ascii-windows-header.csv',
                'expected' => 'file: samples/ascii-windows-header.csv' . PHP_EOL .
                    'size: 12744' . PHP_EOL .
                    'BOM: None' . PHP_EOL .
                    'encoding: ASCII' . PHP_EOL .
                    'line ending: Windows' . PHP_EOL .
                    'lines count: 102' . PHP_EOL .
                    'separator: ,' . PHP_EOL .
                    'enclosure: none' . PHP_EOL .
                    'header: true' . PHP_EOL .
                    'rows count: 100' . PHP_EOL .
                    'columns (14): Region, Country, Item Type, Sales Channel, Order Priority, Order Date, Order ID, Ship Date, Units Sold, Unit Price, Unit Cost, Total Revenue, Total Cost, Total Profit' . PHP_EOL,
            ],
            [
                'file' => 'samples/ascii-mac-header.csv',
                'expected' => 'file: samples/ascii-mac-header.csv' . PHP_EOL .
                    'size: 500' . PHP_EOL .
                    'BOM: None' . PHP_EOL .
                    'encoding: ASCII' . PHP_EOL .
                    'line ending: Mac' . PHP_EOL .
                    'lines count: 9' . PHP_EOL .
                    'separator: ,' . PHP_EOL .
                    'enclosure: "' . PHP_EOL .
                    'header: true' . PHP_EOL .
                    'rows count: 8' . PHP_EOL .
                    'columns (13): Month, Average, 2005, 2006, 2007, 2008, 2009, 2010, 2011, 2012, 2013, 2014, 2015' . PHP_EOL,
            ],
            [
                'file' => 'samples/windows-1252-mac-header.csv',
                'expected' => 'file: samples/windows-1252-mac-header.csv' . PHP_EOL .
                    'size: 523' . PHP_EOL .
                    'BOM: None' . PHP_EOL .
                    'encoding: Windows-1252' . PHP_EOL .
                    'line ending: Mac' . PHP_EOL .
                    'lines count: 9' . PHP_EOL .
                    'separator: ;' . PHP_EOL .
                    'enclosure: \'' . PHP_EOL .
                    'header: true' . PHP_EOL .
                    'rows count: 8' . PHP_EOL .
                    'columns (13): mois, moyenne, 2005, 2006, 2007, 2008, 2009, 2010, 2011, 2012, 2013, 2014, 2015' . PHP_EOL,
            ],
            [
                'file' => 'samples/utf16be-windows-header.csv',
                'expected' => 'file: samples/utf16be-windows-header.csv' . PHP_EOL .
                    'size: 115814' . PHP_EOL .
                    'BOM: UTF-16BE' . PHP_EOL .
                    'encoding: UTF-16BE' . PHP_EOL .
                    'line ending: Windows' . PHP_EOL .
                    'lines count: 1036' . PHP_EOL .
                    'separator: ,' . PHP_EOL .
                    'enclosure: "' . PHP_EOL .
                    'header: true' . PHP_EOL .
                    'rows count: 1034' . PHP_EOL .
                    'columns (6): Name, Team, Position, Height(inches), Weight(lbs), Age' . PHP_EOL,
            ],
            [
                'file' => 'samples/utf16le-windows-header.csv',
                'expected' => 'file: samples/utf16le-windows-header.csv' . PHP_EOL .
                    'size: 103358' . PHP_EOL .
                    'BOM: UTF-16LE' . PHP_EOL .
                    'encoding: UTF-16LE' . PHP_EOL .
                    'line ending: Windows' . PHP_EOL .
                    'lines count: 1035' . PHP_EOL .
                    'separator: ,' . PHP_EOL .
                    'enclosure: none' . PHP_EOL .
                    'header: true' . PHP_EOL .
                    'rows count: 1034' . PHP_EOL .
                    'columns (6): Name, Team, Position, Height(inches), Weight(lbs), Age' . PHP_EOL,
            ],
            [
                'file' => 'samples/utf8-windows-header.csv',
                'expected' => 'file: samples/utf8-windows-header.csv' . PHP_EOL .
                    'size: 57933' . PHP_EOL .
                    'BOM: UTF-8' . PHP_EOL .
                    'encoding: UTF-8' . PHP_EOL .
                    'line ending: Windows' . PHP_EOL .
                    'lines count: 1036' . PHP_EOL .
                    'separator: tab' . PHP_EOL .
                    'enclosure: \'' . PHP_EOL .
                    'header: true' . PHP_EOL .
                    'rows count: 1034' . PHP_EOL .
                    'columns (6): Name, Team, Position, Height(inches), Weight(lbs), Age' . PHP_EOL,
            ],
        ];
    }

    /**
     * @dataProvider getFirstRowCases
     *
     * @param string $file
     * @param array  $expected
     */
    public function testReadNextRow(string $file, array $expected) : void
    {
        $csv = new CSV($file);

        $csv->autoDetect();

        static::assertSame($expected, $csv->readNextRow());
    }

    /**
     * @dataProvider getFirstRowCases
     *
     * @param string $file
     * @param array  $expected
     */
    public function testReadFirstRow(string $file, array $expected) : void
    {
        $csv = new CSV($file);

        $csv->autoDetect();

        static::assertSame($expected, $csv->readRow(0));
    }

    public function getFirstRowCases() : array
    {
        return [
            [
                'file' => 'samples/ascii-linux-header.csv',
                'expected' => ['1', '30'],
            ],
            [
                'file' => 'samples/ascii-linux-no-header.csv',
                'expected' => ['1', '30'],
            ],
            [
                'file' => 'samples/ascii-windows-header.csv',
                'expected' => ['Australia and Oceania', 'Tuvalu', 'Baby Food', 'Offline', 'H', '5/28/2010', '669165933', '6/27/2010', '9925', '255.28', '159.42', '2533654.00', '1582243.50', '951410.50'],
            ],
            [
                'file' => 'samples/ascii-mac-header.csv',
                'expected' => ['May', '0.1', '0', '0', '1', '1', '0', '0', '0', '2', '0', '0', '0'],
            ],
            [
                'file' => 'samples/utf16be-windows-header.csv',
                'expected' => ['Adam Donachie', 'BAL', 'Catcher', '74', '180', '22.99'],
            ],
            [
                'file' => 'samples/utf16le-windows-header.csv',
                'expected' => ['小林 竜也', 'BAL', 'Catcher', '74', '180', '22.99'],
            ],
            [
                'file' => 'samples/utf8-windows-header.csv',
                'expected' => ['Adam Donachie', 'BAL', 'Catcher', '74', '180', '22.99'],
            ],
        ];
    }

    /**
     * @dataProvider getFourthRowCases
     *
     * @param string $file
     * @param array  $expected
     */
    public function testReadFourthRow(string $file, array $expected) : void
    {
        $csv = new CSV($file);

        $csv->autoDetect();

        static::assertSame($expected, $csv->readRow(3));
    }

    /**
     * @dataProvider getFourthRowCases
     *
     * @param string $file
     * @param array  $expected
     */
    public function testReadNextRow2(string $file, array $expected) : void
    {
        $csv = new CSV($file);

        $csv->autoDetect();

        $csv->readNextRow();
        $csv->readNextRow();
        $csv->readNextRow();

        static::assertSame($expected, $csv->readNextRow());
    }

    public function getFourthRowCases() : array
    {
        return [
            [
                'file' => 'samples/ascii-linux-header.csv',
                'expected' => ['4', '16'],
            ],
            [
                'file' => 'samples/ascii-linux-no-header.csv',
                'expected' => ['4', '16'],
            ],
            [
                'file' => 'samples/ascii-windows-header.csv',
                'expected' => ['Sub-Saharan Africa', 'Sao Tome and Principe', 'Fruits', 'Online', 'C', '6/20/2014', '514321792', '7/5/2014', '8102', '9.33', '6.92', '75591.66', '56065.84', '19525.82'],
            ],
            [
                'file' => 'samples/ascii-mac-header.csv',
                'expected' => ['Aug', '2.3', '6', '3', '2', '4', '4', '4', '7', '8', '2', '2', '3'],
            ],
            [
                'file' => 'samples/utf16be-windows-header.csv',
                'expected' => ['Kevin Millar', 'BAL', 'First Baseman', '72', '210', '35.43'],
            ],
            [
                'file' => 'samples/utf16le-windows-header.csv',
                'expected' => ['Kevin Millar', 'BAL', 'First Baseman', '72', '210', '35.43'],
            ],
            [
                'file' => 'samples/utf8-windows-header.csv',
                'expected' => ['Kevin Millar', 'BAL', 'First Baseman', '72', '210', '35.43'],
            ],
        ];
    }

    /**
     * @dataProvider getTestNumericRowsCases
     *
     * @param string $file
     * @param array  $expected
     */
    public function testNumericRows(string $file, array $expected) : void
    {
        $csv = new CSV($file);

        $csv
            ->autoDetect()
            ->setConvertNumbers(true);

        static::assertSame($expected, $csv->readRow(3));
    }

    public function getTestNumericRowsCases() : array
    {
        return [
            [
                'file' => 'samples/ascii-linux-header.csv',
                'expected' => [4, 16],
            ],
            [
                'file' => 'samples/ascii-linux-no-header.csv',
                'expected' => [4, 16],
            ],
            [
                'file' => 'samples/ascii-windows-header.csv',
                'expected' => ['Sub-Saharan Africa', 'Sao Tome and Principe', 'Fruits', 'Online', 'C', '6/20/2014', 514321792, '7/5/2014', 8102, 9.33, 6.92, 75591.66, 56065.84, 19525.82],
            ],
            [
                'file' => 'samples/ascii-mac-header.csv',
                'expected' => ['Aug', 2.3, 6, 3, 2, 4, 4, 4, 7, 8, 2, 2, 3],
            ],
            [
                'file' => 'samples/utf16be-windows-header.csv',
                'expected' => ['Kevin Millar', 'BAL', 'First Baseman', 72, 210, 35.43],
            ],
            [
                'file' => 'samples/utf16le-windows-header.csv',
                'expected' => ['Kevin Millar', 'BAL', 'First Baseman', 72, 210, 35.43],
            ],
            [
                'file' => 'samples/utf8-windows-header.csv',
                'expected' => ['Kevin Millar', 'BAL', 'First Baseman', 72, 210, 35.43],
            ],
        ];
    }

    /**
     * @dataProvider getTestAssociativeArrayCases
     *
     * @param string $file
     * @param array  $expected
     */
    public function testAssociativeArray(string $file, array $expected) : void
    {
        $csv = new CSV($file);

        $csv
            ->autoDetect()
            ->setConvertNumbers(true)
            ->setAssociativeArray(true);

        static::assertSame($expected, $csv->readRow(3));
    }

    public function getTestAssociativeArrayCases() : array
    {
        return [
            [
                'file' => 'samples/ascii-linux-header.csv',
                'expected' => [
                    'Game Number' => 4,
                    'Game Length' => 16,
                ],
            ],
            [
                'file' => 'samples/ascii-linux-no-header.csv',
                'expected' => [
                    'column 0' => 4,
                    'column 1' => 16,
                ],
            ],
            [
                'file' => 'samples/ascii-windows-header.csv',
                'expected' => [
                    'Region' => 'Sub-Saharan Africa',
                    'Country' => 'Sao Tome and Principe',
                    'Item Type' => 'Fruits',
                    'Sales Channel' => 'Online',
                    'Order Priority' => 'C',
                    'Order Date' => '6/20/2014',
                    'Order ID' => 514321792,
                    'Ship Date' => '7/5/2014',
                    'Units Sold' => 8102,
                    'Unit Price' => 9.33,
                    'Unit Cost' => 6.92,
                    'Total Revenue' => 75591.66,
                    'Total Cost' => 56065.84,
                    'Total Profit' => 19525.82,
                ],
            ],
            [
                'file' => 'samples/ascii-mac-header.csv',
                'expected' => [
                    'Month' => 'Aug',
                    'Average' => 2.3,
                    2005 => 6,
                    2006 => 3,
                    2007 => 2,
                    2008 => 4,
                    2009 => 4,
                    2010 => 4,
                    2011 => 7,
                    2012 => 8,
                    2013 => 2,
                    2014 => 2,
                    2015 => 3,
                ],
            ],
            [
                'file' => 'samples/utf16be-windows-header.csv',
                'expected' => [
                    'Name' => 'Kevin Millar',
                    'Team' => 'BAL',
                    'Position' => 'First Baseman',
                    'Height(inches)' => 72,
                    'Weight(lbs)' => 210,
                    'Age' => 35.43,
                ],
            ],
            [
                'file' => 'samples/utf16le-windows-header.csv',
                'expected' => [
                    'Name' => 'Kevin Millar',
                    'Team' => 'BAL',
                    'Position' => 'First Baseman',
                    'Height(inches)' => 72,
                    'Weight(lbs)' => 210,
                    'Age' => 35.43,
                ],
            ],
            [
                'file' => 'samples/utf8-windows-header.csv',
                'expected' => [
                    'Name' => 'Kevin Millar',
                    'Team' => 'BAL',
                    'Position' => 'First Baseman',
                    'Height(inches)' => 72,
                    'Weight(lbs)' => 210,
                    'Age' => 35.43,
                ],
            ],
        ];
    }
}
