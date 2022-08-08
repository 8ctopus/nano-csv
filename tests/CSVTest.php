<?php

namespace Oct8pus\CSV;

use PHPUnit\Framework\TestCase;

/**
 * @internal
 *
 * @covers \Oct8pus\CSV\CSV
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
        $csv = new CSV();
        $csv
            ->setFile($file)
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
                    'line ending: Linux' . PHP_EOL
            ],
            [
                'file' => 'samples/ascii-linux-no-header.csv',
                'expected' =>
                    'file: samples/ascii-linux-no-header.csv' . PHP_EOL .
                    'size: 723' . PHP_EOL .
                    'BOM: None' . PHP_EOL .
                    'encoding: ASCII' . PHP_EOL .
                    'line ending: Linux' . PHP_EOL
            ],
            [
                'file' => 'samples/ascii-windows-header.csv',
                'expected' =>
                    'file: samples/ascii-windows-header.csv' . PHP_EOL .
                    'size: 12744' . PHP_EOL .
                    'BOM: None' . PHP_EOL .
                    'encoding: ASCII' . PHP_EOL .
                    'line ending: Windows' . PHP_EOL
            ],
            [
                'file' => 'samples/ascii-mac-header.csv',
                'expected' =>
                    'file: samples/ascii-mac-header.csv' . PHP_EOL .
                    'size: 500' . PHP_EOL .
                    'BOM: None' . PHP_EOL .
                    'encoding: ASCII' . PHP_EOL .
                    'line ending: Mac' . PHP_EOL
            ],
            [
                'file' => 'samples/utf16be-windows-header.csv',
                'expected' =>
                    'file: samples/utf16be-windows-header.csv' . PHP_EOL .
                    'size: 115852' . PHP_EOL .
                    'BOM: UTF-16BE' . PHP_EOL .
                    'encoding: UTF-16BE' . PHP_EOL .
                    'line ending: Windows' . PHP_EOL
            ],
            [
                'file' => 'samples/utf16le-windows-header.csv',
                'expected' =>
                    'file: samples/utf16le-windows-header.csv' . PHP_EOL .
                    'size: 115852' . PHP_EOL .
                    'BOM: UTF-16LE' . PHP_EOL .
                    'encoding: UTF-16LE' . PHP_EOL .
                    'line ending: Windows' . PHP_EOL
            ],
            [
                'file' => 'samples/utf8-windows-header.csv',
                'expected' =>
                    'file: samples/utf8-windows-header.csv' . PHP_EOL .
                    'size: 57928' . PHP_EOL .
                    'BOM: UTF-8' . PHP_EOL .
                    'encoding: UTF-8' . PHP_EOL .
                    'line ending: Windows' . PHP_EOL
            ],
        ];
    }
}
