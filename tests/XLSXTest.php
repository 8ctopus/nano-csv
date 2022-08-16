<?php

namespace Oct8pus\CSV;

use PHPUnit\Framework\TestCase;

/**
 * @internal
 *
 * @covers \Oct8pus\CSV\XLSX
 * @covers \Oct8pus\CSV\CSVException
 */
final class XSLXTest extends TestCase
{
    /**
     * @dataProvider getAutoDetectCases
     *
     * @param string $file
     * @param string $expected
     */
    public function testAutoDetect(string $file, string $expected) : void
    {
        $xlsx = new XLSX($file);
        $xlsx->autoDetect();

        //echo $xlsx;

        $this->assertSame($expected, (string) $xlsx);
    }

    public function getAutoDetectCases() : array
    {
        return [
            [
                'file' => 'samples/test.xlsx',
                'expected' =>
                    'file: ' . sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'test.csv' . PHP_EOL .
                    'size: 174' . PHP_EOL .
                    'BOM: UTF-8' . PHP_EOL .
                    'encoding: UTF-8' . PHP_EOL .
                    'line ending: Linux' . PHP_EOL .
                    'lines count: 9' . PHP_EOL .
                    'separator: ,' . PHP_EOL .
                    'enclosure: none' . PHP_EOL .
                    'header: true' . PHP_EOL .
                    'rows count: 7' . PHP_EOL .
                    'columns (5): name, class, weight, empty, height' . PHP_EOL,
            ],
        ];
    }
}
