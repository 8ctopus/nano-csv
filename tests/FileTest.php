<?php

namespace Oct8pus\CSV;

use PHPUnit\Framework\TestCase;

/**
 * @internal
 *
 * @covers \Oct8pus\CSV\BOM
 * @covers \Oct8pus\CSV\File
 * @covers \Oct8pus\CSV\FileException
 * @covers \Oct8pus\CSV\LineEnding
 */
final class FileTest extends TestCase
{
    /**
     * @dataProvider getAutoDetectCases
     *
     * @param string $file
     * @param string $expected
     */
    public function testAutoDetect(string $file, string $expected) : void
    {
        $file = new File($file);
        $file
            ->autoDetect();

        //echo $file;

        $this->assertEquals($expected, (string) $file);
    }

    public function getAutoDetectCases() : array
    {
        return [
            [
                'file' => 'samples/ascii-linux-header.csv',
                'expected' =>
                    'file: samples/ascii-linux-header.csv' . PHP_EOL .
                    'size: 723' . PHP_EOL .
                    'BOM: None' . PHP_EOL .
                    'encoding: ASCII' . PHP_EOL .
                    'line ending: Linux' . PHP_EOL,            ],
            [
                'file' => 'samples/ascii-linux-no-header.csv',
                'expected' =>
                    'file: samples/ascii-linux-no-header.csv' . PHP_EOL .
                    'size: 694' . PHP_EOL .
                    'BOM: None' . PHP_EOL .
                    'encoding: ASCII' . PHP_EOL .
                    'line ending: Linux' . PHP_EOL,
            ],
            [
                'file' => 'samples/ascii-windows-header.csv',
                'expected' =>
                    'file: samples/ascii-windows-header.csv' . PHP_EOL .
                    'size: 12744' . PHP_EOL .
                    'BOM: None' . PHP_EOL .
                    'encoding: ASCII' . PHP_EOL .
                    'line ending: Windows' . PHP_EOL,
            ],
            [
                'file' => 'samples/ascii-mac-header.csv',
                'expected' =>
                    'file: samples/ascii-mac-header.csv' . PHP_EOL .
                    'size: 500' . PHP_EOL .
                    'BOM: None' . PHP_EOL .
                    'encoding: ASCII' . PHP_EOL .
                    'line ending: Mac' . PHP_EOL,
            ],
            [
                'file' => 'samples/utf16be-windows-header.csv',
                'expected' =>
                    'file: samples/utf16be-windows-header.csv' . PHP_EOL .
                    'size: 115814' . PHP_EOL .
                    'BOM: UTF-16BE' . PHP_EOL .
                    'encoding: UTF-16BE' . PHP_EOL .
                    'line ending: Windows' . PHP_EOL,
            ],
            [
                'file' => 'samples/utf16le-windows-header.csv',
                'expected' =>
                    'file: samples/utf16le-windows-header.csv' . PHP_EOL .
                    'size: 115810' . PHP_EOL .
                    'BOM: UTF-16LE' . PHP_EOL .
                    'encoding: UTF-16LE' . PHP_EOL .
                    'line ending: Windows' . PHP_EOL,
            ],
            [
                'file' => 'samples/utf8-windows-header.csv',
                'expected' =>
                    'file: samples/utf8-windows-header.csv' . PHP_EOL .
                    'size: 57933' . PHP_EOL .
                    'BOM: UTF-8' . PHP_EOL .
                    'encoding: UTF-8' . PHP_EOL .
                    'line ending: Windows' . PHP_EOL,
            ],
        ];
    }

    /**
     * @dataProvider getFirstLineCases
     *
     * @param string $file
     * @param string $expected
     */
    public function testReadNextRow(string $file, string $expected) : void
    {
        $file = new File($file);
        $file
            ->autoDetect();

        $this->assertEquals($expected, $file->readNextLine());
    }

    /**
     * @dataProvider getFirstLineCases
     *
     * @param string $file
     * @param string $expected
     */
    public function testReadFirstRow(string $file, string $expected) : void
    {
        $file = new File($file);
        $file
            ->autoDetect();

        $this->assertEquals($expected, $file->readLine(0));
    }

    public function getFirstLineCases() : array
    {
        return [
            [
                'file' => 'samples/ascii-linux-header.csv',
                'expected' => '"Game Number", "Game Length"',
            ],
            [
                'file' => 'samples/ascii-linux-no-header.csv',
                'expected' => '1, 30',
            ],
            [
                'file' => 'samples/ascii-windows-header.csv',
                'expected' => 'Region,Country,Item Type,Sales Channel,Order Priority,Order Date,Order ID,Ship Date,Units Sold,Unit Price,Unit Cost,Total Revenue,Total Cost,Total Profit',
            ],
            [
                'file' => 'samples/ascii-mac-header.csv',
                'expected' => '"Month", "Average", "2005", "2006", "2007", "2008", "2009", "2010", "2011", "2012", "2013", "2014", "2015"',
            ],
            [
                'file' => 'samples/utf16be-windows-header.csv',
                'expected' => '"Name", "Team", "Position", "Height(inches)", "Weight(lbs)", "Age"',
            ],
            [
                'file' => 'samples/utf16le-windows-header.csv',
                'expected' => '"Name", "Team", "Position", "Height(inches)", "Weight(lbs)", "Age"',
            ],
            [
                'file' => 'samples/utf8-windows-header.csv',
                'expected' => '"Name", "Team", "Position", "Height(inches)", "Weight(lbs)", "Age"',
            ],
        ];
    }

    /**
     * @dataProvider getSecondLineCases
     *
     * @param string $file
     * @param string $expected
     */
    public function testReadNextLine2(string $file, string $expected) : void
    {
        $file = new File($file);
        $file
            ->autoDetect();

        $file->readNextLine();

        $this->assertEquals($expected, $file->readNextLine());
    }

    /**
     * @dataProvider getSecondLineCases
     *
     * @param string $file
     * @param string $expected
     */
    public function testReadSecondLine(string $file, string $expected) : void
    {
        $file = new File($file);
        $file
            ->autoDetect();

        $this->assertEquals($expected, $file->readLine(1));
    }

    public function getSecondLineCases() : array
    {
        return [
            [
                'file' => 'samples/ascii-linux-header.csv',
                'expected' => '1, 30',
            ],
            [
                'file' => 'samples/ascii-linux-no-header.csv',
                'expected' => '2, 29',
            ],
            [
                'file' => 'samples/ascii-windows-header.csv',
                'expected' => 'Australia and Oceania,Tuvalu,Baby Food,Offline,H,5/28/2010,669165933,6/27/2010,9925,255.28,159.42,2533654.00,1582243.50,951410.50',
            ],
            [
                'file' => 'samples/ascii-mac-header.csv',
                'expected' => '"May",  0.1,  0,  0, 1, 1, 0, 0, 0, 2, 0,  0,  0  ',
            ],
            [
                'file' => 'samples/utf16be-windows-header.csv',
                'expected' => '"Adam Donachie", "BAL", "Catcher", 74, 180, 22.99',
            ],
            [
                'file' => 'samples/utf16le-windows-header.csv',
                'expected' => '"Adam Donachie", "BAL", "Catcher", 74, 180, 22.99',
            ],
            [
                'file' => 'samples/utf8-windows-header.csv',
                'expected' => '"Adam Donachie", "BAL", "Catcher", 74, 180, 22.99',
            ],
        ];
    }

    /**
     * @dataProvider getLinesCountCases
     *
     * @param string $file
     * @param int $expected
     */
    public function testLinesCount(string $file, int $expected) : void
    {
        $file = new File($file);
        $file
            ->autoDetect();

        $this->assertEquals($expected, $file->linesCount());
    }

    public function getLinesCountCases() : array
    {
        return [
            [
                'file' => 'samples/ascii-linux-header.csv',
                'expected' => 102,
            ],
            [
                'file' => 'samples/ascii-linux-no-header.csv',
                'expected' => 101,
            ],
            [
                'file' => 'samples/ascii-windows-header.csv',
                'expected' => 102,
            ],
            [
                'file' => 'samples/ascii-mac-header.csv',
                'expected' => 9,
            ],
            [
                'file' => 'samples/utf16be-windows-header.csv',
                'expected' => 1036,
            ],
            [
                'file' => 'samples/utf16le-windows-header.csv',
                'expected' => 1035,
            ],
            [
                'file' => 'samples/utf8-windows-header.csv',
                'expected' => 1036,
            ],
        ];
    }
}
