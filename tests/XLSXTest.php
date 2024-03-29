<?php

declare(strict_types=1);

namespace Oct8pus\CSV;

use PHPUnit\Framework\TestCase;

/**
 * @internal
 *
 * @covers \Oct8pus\CSV\CSVException
 * @covers \Oct8pus\CSV\XLSX
 */
final class XLSXTest extends TestCase
{
    /**
     * @dataProvider getAutoDetectCases
     *
     * @param string $file
     * @param string $expected
     *
     * @return void
     */
    public function testAutoDetect(string $file, string $expected) : void
    {
        $xlsx = new XLSX($file);
        $xlsx->autoDetect();

        //echo $xlsx;

        self::assertSame($expected, (string) $xlsx);

        // test loop (detect incomplete rows)
        while (/*$row = */ $xlsx->readNextRow());
        //echo implode(', ', $row) . PHP_EOL;
    }

    public static function getAutoDetectCases() : array
    {
        return [
            [
                'file' => 'samples/test.xlsx',
                'expected' => 'file: ' . sys_get_temp_dir() . \DIRECTORY_SEPARATOR . 'test.csv' . PHP_EOL .
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
            [
                'file' => 'samples/test2.xlsx',
                'expected' => 'file: ' . sys_get_temp_dir() . \DIRECTORY_SEPARATOR . 'test2.csv' . PHP_EOL .
                    'size: 1009' . PHP_EOL .
                    'BOM: UTF-8' . PHP_EOL .
                    'encoding: UTF-8' . PHP_EOL .
                    'line ending: Linux' . PHP_EOL .
                    'lines count: 9' . PHP_EOL .
                    'separator: ,' . PHP_EOL .
                    'enclosure: none' . PHP_EOL .
                    'header: true' . PHP_EOL .
                    'rows count: 7' . PHP_EOL .
                    'columns (12): TransactionID, TransactionDate, MerchantAccName, BillingDescriptor, PaymentType, OrderID, Amount, CurrencySymbol, CardBrand, Result, ResponseCode, ResponseDescription' . PHP_EOL,
            ],
        ];
    }

    /**
     * @dataProvider getHeaderCases()
     *
     * @param string $file
     * @param array  $expected
     *
     * @return void
     */
    public function testHeader(string $file, array $expected) : void
    {
        $xlsx = new XLSX($file);
        $xlsx->autoDetect();

        //echo $xlsx;

        self::assertSame($expected, $xlsx->getColumns());
    }

    public static function getHeaderCases() : array
    {
        return [
            [
                'file' => 'samples/test.xlsx',
                'expected' => [
                    'name', 'class', 'weight', 'empty', 'height',
                ],
            ],
            [
                'file' => 'samples/test2.xlsx',
                'expected' => [
                    'TransactionID', 'TransactionDate', 'MerchantAccName', 'BillingDescriptor', 'PaymentType', 'OrderID', 'Amount', 'CurrencySymbol', 'CardBrand', 'Result', 'ResponseCode', 'ResponseDescription',
                ],
            ],
        ];
    }
}
