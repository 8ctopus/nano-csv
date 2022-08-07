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
     */
    public function testDetect(string $file, $expected) : void
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
                'file' => 'samples/ascii-linux.csv',
                'expected' =>
                    'file: samples/ascii-linux.csv' . PHP_EOL .
                    'size: 723' . PHP_EOL .
                    'BOM: None' . PHP_EOL .
                    'encoding: ASCII' . PHP_EOL .
                    'line ending: Linux' . PHP_EOL
            ],
            [
                'file' => 'samples/ascii-windows.csv',
                'expected' =>
                    'file: samples/ascii-windows.csv' . PHP_EOL .
                    'size: 12744' . PHP_EOL .
                    'BOM: None' . PHP_EOL .
                    'encoding: ASCII' . PHP_EOL .
                    'line ending: Windows' . PHP_EOL
            ],
            [
                'file' => 'samples/ascii-mac.csv',
                'expected' =>
                    'file: samples/ascii-mac.csv' . PHP_EOL .
                    'size: 500' . PHP_EOL .
                    'BOM: None' . PHP_EOL .
                    'encoding: ASCII' . PHP_EOL .
                    'line ending: Mac' . PHP_EOL
            ],
            [
                'file' => 'samples/utf16be-windows.csv',
                'expected' =>
                    'file: samples/utf16be-windows.csv' . PHP_EOL .
                    'size: 115852' . PHP_EOL .
                    'BOM: UTF-16BE' . PHP_EOL .
                    'encoding: UTF-16BE' . PHP_EOL .
                    'line ending: Windows' . PHP_EOL
            ],
            [
                'file' => 'samples/utf16le-windows.csv',
                'expected' =>
                    'file: samples/utf16le-windows.csv' . PHP_EOL .
                    'size: 115852' . PHP_EOL .
                    'BOM: UTF-16LE' . PHP_EOL .
                    'encoding: UTF-16LE' . PHP_EOL .
                    'line ending: Windows' . PHP_EOL
            ],
            [
                'file' => 'samples/utf8-windows.csv',
                'expected' =>
                    'file: samples/utf8-windows.csv' . PHP_EOL .
                    'size: 57928' . PHP_EOL .
                    'BOM: UTF-8' . PHP_EOL .
                    'encoding: UTF-8' . PHP_EOL .
                    'line ending: Windows' . PHP_EOL
            ],
        ];
    }
}
