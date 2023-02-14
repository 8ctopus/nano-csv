<?php

namespace Oct8pus\CSV;

use XMLReader;
use ZipArchive;

class XLSX extends CSV
{
    /**
     * Constructor
     *
     * @param string $file
     *
     * @return self
     */
    public function __construct(string $file)
    {
        $extension = pathinfo($file, PATHINFO_EXTENSION);

        if ($extension !== 'xlsx') {
            throw new CSVException("invalid extension {$extension}");
        }

        // extract xls into array
        $table = $this->extract($file);

        $file = sys_get_temp_dir() . \DIRECTORY_SEPARATOR . pathinfo($file, PATHINFO_FILENAME) . '.csv';

        // convert to csv in temp dir
        $this->convert($table, $file);

        parent::__construct($file);
    }

    /**
     * Extract xlsx data
     *
     * @param string $file
     *
     * @return array
     *
     * @throws CSVException
     */
    private function extract(string $file) : array
    {
        $zip = new ZipArchive();

        $result = $zip->open($file, ZipArchive::RDONLY);

        if ($result !== true) {
            switch ($result) {
                case ZipArchive::ER_OK:
                    $result = 'No error';
                    break;

                case ZipArchive::ER_MULTIDISK:
                    $result = 'Multi-disk zip archives not supported';
                    break;

                case ZipArchive::ER_RENAME:
                    $result = 'Renaming temporary file failed';
                    break;

                case ZipArchive::ER_CLOSE:
                    $result = 'Closing zip archive failed';
                    break;

                case ZipArchive::ER_SEEK:
                    $result = 'Seek error';
                    break;

                case ZipArchive::ER_READ:
                    $result = 'Read error';
                    break;

                case ZipArchive::ER_WRITE:
                    $result = 'Write error';
                    break;

                case ZipArchive::ER_CRC:
                    $result = 'CRC error';
                    break;

                case ZipArchive::ER_ZIPCLOSED:
                    $result = 'Containing zip archive was closed';
                    break;

                case ZipArchive::ER_NOENT:
                    $result = 'No such file';
                    break;

                case ZipArchive::ER_EXISTS:
                    $result = 'File already exists';
                    break;

                case ZipArchive::ER_OPEN:
                    $result = 'Can\'t open file';
                    break;

                case ZipArchive::ER_TMPOPEN:
                    $result = 'Failure to create temporary file';
                    break;

                case ZipArchive::ER_ZLIB:
                    $result = 'Zlib error';
                    break;

                case ZipArchive::ER_MEMORY:
                    $result = 'Malloc failure';
                    break;

                case ZipArchive::ER_CHANGED:
                    $result = 'Entry has been changed';
                    break;

                case ZipArchive::ER_COMPNOTSUPP:
                    $result = 'Compression method not supported';
                    break;

                case ZipArchive::ER_EOF:
                    $result = 'Premature EOF';
                    break;

                case ZipArchive::ER_INVAL:
                    $result = 'Invalid argument';
                    break;

                case ZipArchive::ER_NOZIP:
                    $result = 'Not a zip archive';
                    break;

                case ZipArchive::ER_INTERNAL:
                    $result = 'Internal error';
                    break;

                case ZipArchive::ER_INCONS:
                    $result = 'Zip archive inconsistent';
                    break;

                case ZipArchive::ER_REMOVE:
                    $result = 'Can\'t remove file';
                    break;

                case ZipArchive::ER_DELETED:
                    $result = 'Entry has been deleted';
                    break;

                default:
                    $result = sprintf('Unknown status %s', $result);
                    break;
            }

            throw new CSVException("open xlsx - {$result}");
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
                    throw new CSVException('extract xlsx');
                }
            }
        }

        $zip->close();

        // parse shared strings
        $xml = new XMLReader();

        $xml->open(sys_get_temp_dir() . \DIRECTORY_SEPARATOR . $list[1]);

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
        $xml->open(sys_get_temp_dir() . \DIRECTORY_SEPARATOR . $list[0]);

        $table = [];
        $path = [];
        $row = [];
        $columnsCount = null;
        $sharedString = null;

        while ($xml->read()) {
            switch ($xml->nodeType) {
                case XMLReader::ELEMENT:
                    $path[] = $xml->name;

                    // create row
                    if ($xml->name === 'row') {
                        $row = [];
                    } elseif ($xml->name === 'c') {
                        // cell value can be either a shared string or a number
                        $sharedString = $xml->getAttribute('t') === 's';

                        // get cell column
                        $column = $xml->getAttribute('r')[0];

                        // insert empty cells if any
                        $diff = ord($column) - ord('A') - count($row);

                        for ($i = 0; $i < $diff; ++$i) {
                            $row[] = '';
                        }

                        // add cell
                        $row[] = '';
                    }

                    break;

                case XMLReader::END_ELEMENT:
                    array_pop($path);

                    // add row to rows
                    if ($xml->name === 'row') {
                        if (empty($table)) {
                            $columnsCount = count($row);
                        }

                        // complete not full rows
                        for ($i = count($row); $i < $columnsCount; ++$i) {
                            $row[] = '';
                        }

                        $table[] = $row;
                    }

                    break;

                case XMLReader::TEXT:
                    if (array_slice($path, -4, null, false) === ['sheetData', 'row', 'c', 'v']) {
                        // add cell text
                        $row[count($row) - 1] = $sharedString ? $shared[$xml->value] : $xml->value;
                    }

                    break;
            }

            // empty elements are self-closing and do not have XMLReader::END_ELEMENT
            if ($xml->isEmptyElement) {
                array_pop($path);
            }
        }

        return $table;
    }

    /**
     * Convert to csv
     *
     * @param array  $table
     * @param string $file
     *
     * @return void
     *
     * @throws CSVException
     */
    private function convert(array $table, string $file) : void
    {
        // open file
        $handle = fopen($file, 'w', false, null);

        if ($handle === false) {
            throw new CSVException('open file');
        }

        // write utf8 BOM
        $bom = [0xEF, 0xBB, 0xBF];
        $str = '';

        foreach ($bom as $byte) {
            $str .= chr($byte);
        }

        if (fwrite($handle, $str, 3) === false) {
            throw new CSVException('write BOM');
        }

        $separator = ',';
        $enclosure = '"';
        $escape = '\\';
        $eol = "\n";

        foreach ($table as $row) {
            $line = '';

            // escape enclosure
            foreach ($row as &$cell) {
                if (str_contains($cell, $enclosure)) {
                    $cell = str_replace($enclosure, $escape . $enclosure, $cell);

                    // enclose
                    $cell = $enclosure . $cell . $enclosure;
                } elseif (str_contains($cell, $separator)) {
                    // enclose
                    $cell = $enclosure . $cell . $enclosure;
                }
            }

            // convert row to line
            $line = implode($separator, $row) . $eol;

            // write line
            if (fwrite($handle, $line, null) === false) {
                throw new CSVException('write line');
            }
        }

        if (!fclose($handle)) {
            throw new CSVException('close file');
        }
    }
}
