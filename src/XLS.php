<?php

namespace Oct8pus\CSV;

use XMLReader;
use ZipArchive;

class XLS
{
    private string $file;

    /**
     * Constructor
     *
     * @param string $file
     *
     * @return self
     */
    public function __construct(string $file)
    {
        $this->file = $file;

        $table = $this->extract();

        var_dump($table);
    }

    /**
     * Extract
     *
     * @throws CSVException
     *
     * @return array
     */
    private function extract() : array
    {
        $zip = new ZipArchive();

        if (!$zip->open($this->file)) {
            throw new CSVException();
        }

        // extract required data
        $list = [
            'xl/worksheets/sheet1.xml',
            'xl/sharedStrings.xml',
        ];

        for ($i = 0; $i < $zip->count(); ++$i) {
            $name = $zip->getNameIndex($i);

            if (in_array($name, $list, true)) {
                $result = $zip->extractTo(sys_get_temp_dir(), $name);

                if (!$result) {
                    throw new CSVException();
                }

            }
        }

        $zip->close();

        // parse shared strings
        $xml = new XMLReader();

        $xml->open(sys_get_temp_dir() . DIRECTORY_SEPARATOR . $list[1]);

        $shared = [];
        $path = [];

        while ($xml->read()) {
            switch ($xml->nodeType) {
                case XMLReader::ELEMENT:
                    $path[] = $xml->name;
                    break;

                case XMLReader::END_ELEMENT:
                    array_pop($path);
                    break;

                case XMLReader::TEXT:
                    if (array_slice($path, -2, null, false) === ['si', 't']) {
                        $shared[] = $xml->value;
                    }

                    break;
            }
        }

        // parse sheet
        // <sheetData>
        // <row> row
        // <c r="A1" t="s"> cell using string value form shared strings
        // <c r="B1"> cell using number value
        // <v> value
        $xml->open(sys_get_temp_dir() . DIRECTORY_SEPARATOR . $list[0]);

        $table = [];
        $path = [];
        $row;
        $column;
        $cellColumn;
        $sharedString;

        while ($xml->read()) {
            switch ($xml->nodeType) {
                case XMLReader::ELEMENT:
                    $path[] = $xml->name;

                    // create row
                    if ($xml->name === 'row') {
                        $row = [];
                        $column = 'A';
                    } elseif ($xml->name === 'c') {
                        // cell value can be either a shared string or a number
                        $sharedString = $xml->getAttribute('t') === 's';

                        // get cell column
                        $cellColumn = $xml->getAttribute('r')[0];
                    }

                    break;

                case XMLReader::END_ELEMENT:
                    array_pop($path);

                    // add row to rows
                    if ($xml->name === 'row') {
                        $table[] = $row;
                    }

                    break;

                case XMLReader::TEXT:
                    if (array_slice($path, -4, null, false) === ['sheetData', 'row', 'c', 'v']) {
                        if ($cellColumn !== $column) {
                            // insert empty cells
                            $diff = ord($cellColumn) - ord($column);

                            for ($i = 0; $i < $diff; ++$i) {
                                $row[] = '';
                            }

                            $column = $cellColumn;
                        } else {
                            $column = chr(ord($column) + 1);
                        }

                        // add cell
                        $row[] = $sharedString ? $shared[$xml->value] : $xml->value;
                    }

                    break;
            }
        }

        return $table;
    }
}
