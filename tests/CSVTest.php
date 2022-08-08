<?php

namespace Oct8pus\CSV;

use PHPUnit\Framework\TestCase;

/**
 * @internal
 *
 * @covers \Oct8pus\CSV\CSV
 * @covers \Oct8pus\CSV\BOM
 * @covers \Oct8pus\CSV\LineEnding
 * @covers \Oct8pus\CSV\CSVException
 */
final class CSVTest extends TestCase
{
    /**
     * @dataProvider getDetectCases
     *
     * @param string $file
     * @param string $expected
     */
    public function testDetect(string $file, string $expected) : void
    {
        $csv = new CSV($file);
        $csv
            ->autoDetect();

        //echo $csv;

        $this->assertEquals($expected, (string) $csv);
    }

    public function getDetectCases() : array
    {
        return [
            [
                'file' => 'samples/ascii-linux-header.csv',
                'expected' =>
                    'file: samples/ascii-linux-header.csv' . PHP_EOL .
                    'size: 723' . PHP_EOL .
                    'BOM: None' . PHP_EOL .
                    'encoding: ASCII' . PHP_EOL .
                    'line ending: Linux' . PHP_EOL .
                    'separator: ,' . PHP_EOL .
                    'enclosure: "' . PHP_EOL .
                    'header: true' . PHP_EOL .
                    'columns (2): Game Number, Game Length' . PHP_EOL
            ],
            [
                'file' => 'samples/ascii-linux-no-header.csv',
                'expected' =>
                    'file: samples/ascii-linux-no-header.csv' . PHP_EOL .
                    'size: 694' . PHP_EOL .
                    'BOM: None' . PHP_EOL .
                    'encoding: ASCII' . PHP_EOL .
                    'line ending: Linux' . PHP_EOL .
                    'separator: ,' . PHP_EOL .
                    'enclosure: ' . PHP_EOL .
                    'header: false' . PHP_EOL .
                    'columns (2): column 0, column 1' . PHP_EOL
            ],
            [
                'file' => 'samples/ascii-windows-header.csv',
                'expected' =>
                    'file: samples/ascii-windows-header.csv' . PHP_EOL .
                    'size: 12744' . PHP_EOL .
                    'BOM: None' . PHP_EOL .
                    'encoding: ASCII' . PHP_EOL .
                    'line ending: Windows' . PHP_EOL .
                    'separator: ,' . PHP_EOL .
                    'enclosure: ' . PHP_EOL .
                    'header: true' . PHP_EOL .
                    'columns (14): Region, Country, Item Type, Sales Channel, Order Priority, Order Date, Order ID, Ship Date, Units Sold, Unit Price, Unit Cost, Total Revenue, Total Cost, Total Profit' . PHP_EOL
            ],
            [
                'file' => 'samples/ascii-mac-header.csv',
                'expected' =>
                    'file: samples/ascii-mac-header.csv' . PHP_EOL .
                    'size: 500' . PHP_EOL .
                    'BOM: None' . PHP_EOL .
                    'encoding: ASCII' . PHP_EOL .
                    'line ending: Mac' . PHP_EOL .
                    'separator: ,' . PHP_EOL .
                    'enclosure: "' . PHP_EOL .
                    'header: true' . PHP_EOL .
                    'columns (13): Month, Average, 2005, 2006, 2007, 2008, 2009, 2010, 2011, 2012, 2013, 2014, 2015' . PHP_EOL
            ],
            [
                'file' => 'samples/utf16be-windows-header.csv',
                'expected' =>
                    'file: samples/utf16be-windows-header.csv' . PHP_EOL .
                    'size: 115852' . PHP_EOL .
                    'BOM: UTF-16BE' . PHP_EOL .
                    'encoding: UTF-16BE' . PHP_EOL .
                    'line ending: Windows' . PHP_EOL .
                    'separator: ,' . PHP_EOL .
                    'enclosure: "' . PHP_EOL .
                    'header: true' . PHP_EOL .
                    'columns (6): Name, Team, Position, Height(inches), Weight(lbs), Age' . PHP_EOL
            ],
            [
                'file' => 'samples/utf16le-windows-header.csv',
                'expected' =>
                    'file: samples/utf16le-windows-header.csv' . PHP_EOL .
                    'size: 115852' . PHP_EOL .
                    'BOM: UTF-16LE' . PHP_EOL .
                    'encoding: UTF-16LE' . PHP_EOL .
                    'line ending: Windows' . PHP_EOL .
                    'separator: ,' . PHP_EOL .
                    'enclosure: "' . PHP_EOL .
                    'header: true' . PHP_EOL .
                    'columns (6): Name, Team, Position, Height(inches), Weight(lbs), Age' . PHP_EOL
            ],
            [
                'file' => 'samples/utf8-windows-header.csv',
                'expected' =>
                    'file: samples/utf8-windows-header.csv' . PHP_EOL .
                    'size: 57928' . PHP_EOL .
                    'BOM: UTF-8' . PHP_EOL .
                    'encoding: UTF-8' . PHP_EOL .
                    'line ending: Windows' . PHP_EOL .
                    'separator: ,' . PHP_EOL .
                    'enclosure: "' . PHP_EOL .
                    'header: true' . PHP_EOL .
                    'columns (6): Name, Team, Position, Height(inches), Weight(lbs), Age' . PHP_EOL
            ],
        ];
    }
}
