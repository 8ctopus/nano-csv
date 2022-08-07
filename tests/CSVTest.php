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
    public function testConstructor()
    {
        $this->assertEquals(1, 1);
    }

    /**
     * @dataProvider getCases
     */
    public function testFiles(string $file, $expected)
    {
        $csv = new CSV();
        $csv
            ->setFile($file)
            ->autoDetect();

        echo $csv;

        $this->assertEquals($expected, (string) $csv);
    }

    public function getCases()
    {
        return [
            [
                'file' => 'samples/ascii-linux.csv',
                'expected' =>
                    'file: samples/ascii-linux.csv' . PHP_EOL .
                    'size: 723' . PHP_EOL .
                    'bom: None' . PHP_EOL .
                    'ending: Linux' . PHP_EOL
            ],
            [
                'file' => 'samples/ascii-windows.csv',
                'expected' =>
                    'file: samples/ascii-windows.csv' . PHP_EOL .
                    'size: 12744' . PHP_EOL .
                    'bom: None' . PHP_EOL .
                    'ending: Windows' . PHP_EOL
            ],
            [
                'file' => 'samples/ascii-mac.csv',
                'expected' =>
                    'file: samples/ascii-mac.csv' . PHP_EOL .
                    'size: 500' . PHP_EOL .
                    'bom: None' . PHP_EOL .
                    'ending: Mac' . PHP_EOL
            ],
        ];
    }
}
