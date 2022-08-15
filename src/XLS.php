<?php

namespace Oct8pus\CSV;

use XMLReader;
use ZipArchive;

class XLS extends File
{
    private bool $header;
    private array $columns;
    private int $columnsCount;

    private int $rowsCount;
    private bool $convertNumbers;
    private bool $associativeArray;

    /**
     * Constructor
     *
     * @param string $file
     *
     * @return self
     */
    public function __construct(string $file)
    {
        $this->associativeArray = false;
        $this->convertNumbers = false;

        parent::__construct($file);
    }

    /**
     * Debug
     *
     * @return string
     */
    public function __toString() : string
    {
        $columns = implode(', ', $this->columns);
        $header = $this->header ? 'true' : 'false';

        return
            parent::__toString() .
            "header: {$header}" . PHP_EOL .
            "rows count: {$this->rowsCount()}" . PHP_EOL .
            "columns ({$this->columnsCount}): {$columns}" . PHP_EOL;
    }

    /**
     * Get/set property
     *
     * @param string $method
     * @param array  $args
     *
     * @return mixed|void
     */
    public function __call(string $method, array $args)
    {
        $operation = substr($method, 0, 3);

        $property = str_replace(['get', 'set'], '', $method);
        $property = lcfirst($property);

        switch ($operation) {
            case 'get':
                if (property_exists($this, $property)) {
                    return $this->{$property};
                } else {
                    return parent::__call($method, $args);
                }

            case 'set':
                if (in_array($property, [
                    'separator',
                    'enclosure',
                    'escape',
                    'columns',
                    'columnsCount',
                ], true)) {
                    if (!isset($this->{$property})) {
                        $this->{$property} = $args[0];
                        return $this;
                    }

                    throw new CSVException("property {$property} cannot be updated");
                } elseif (in_array($property, [
                    'convertNumbers',
                    'associativeArray',
                ], true)) {
                    $this->{$property} = $args[0];
                    return $this;
                }

            default:
                throw new CSVException("unknown property {$property}");
        }
    }

    /**
     * Autodetect properties
     *
     * @throws CSVException
     *
     * @return self
     */
    public function autoDetect() : self
    {
        parent::autoDetect();

        $this->header = $this->detectHeader();

        $this->columnsCount = count($this->readColumns());

        if ($this->header) {
            $this->columns = $this->readColumns();
            $this->trimColumns();

            // skip header
            $this->readCurrentLine(false);
        } else {
            $this->columns = [];

            for ($i = 0; $i < $this->columnsCount; ++$i) {
                $this->columns[] = "column {$i}";
            }
        }

        return $this;
    }

    /**
     * Extract
     *
     * @throws CSVException
     *
     * @return self
     */
    public function extract() : self
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

        /*
        $handle = fopen(sys_get_temp_dir() . DIRECTORY_SEPARATOR . $list[0], 'r', false);

        if ($handle === false) {
            throw new CSVException();
        }

        $stat = fstat($handle);

        $data = fread($handle, $stat['size']);
        */

        $xml = new XMLReader();

        $xml->open(sys_get_temp_dir() . DIRECTORY_SEPARATOR . $list[0]);

        // <sheetData>
        // <row> row
        // <c r="A1" t="s"> cell using string value form shared strings
        // <c r="B1"> cell using number value
        // <v> value

        // go to sheetData
        //$xml->next('sheetData');

        $rows;
        $type;

        while ($xml->read()) {
            if ($xml->nodeType === XMLReader::END_ELEMENT) {
                switch ($xml->name) {
                    case 'row':
                        $rows[] = $row;
                        unset($row);
                        break;

                    default:
                }
            } else if ($xml->nodeType === XMLReader::ELEMENT) {
                switch ($xml->name) {
                    case 'row':
                        $row = [];
                        break;

                    case 'c':
                        if ($xml->hasAttributes) {
                            $type = 'value';

                            while ($xml->moveToNextAttribute()) {
                                if ($xml->name === 't') {
                                    $type = 'shared';
                                }
                            }
                        }

                        break;

                    case 'v':
                        $cell = true;
                        break;

                    default:
                }
            } else if ($xml->nodeType === XMLReader::TEXT) {
                if ($cell) {
                    $row[] = $xml->value;
                    $cell = false;
                }
            }
        }

        var_dump($rows);
    }

    /**
     * Read row
     *
     * @param int  $row
     *
     * @return array
     */
    public function readRow(int $row) : array
    {
        if (isset($this->header) && $this->header) {
            ++$row;
        }

        if (isset($this->rowsCount) && $row >= $this->rowsCount) {
            throw new CSVException("out of bounds {$row} / {$this->rowsCount}");
        }

        $line = parent::readLine($row);

        return $this->lineToArray($line);
    }

    /**
     * Read next row
     *
     * @return ?array
     */
    public function readNextRow() : ?array
    {
        $line = parent::readCurrentLine(false);

        if (!$line) {
            return null;
        }

        return $this->lineToArray($line);
    }

    /**
     * Read rows count without header
     *
     * @return int
     */
    public function rowsCount() : int
    {
        // get cached value
        if (isset($this->linesCount)) {
            return $this->linesCount;
        }

        // save offset
        $offset = $this->currentOffset;

        // seek to data start
        $this->seek($this->startOffset);

        $rowsCount = 0;

        while (($line = $this->readNextLine()) !== null) {
            if ($line !== '') {
                ++$rowsCount;
            }
        }

        $this->rowsCount = $rowsCount - ($this->header ? 1 : 0);

        // seek back to saved offset
        $this->seek($offset);

        return $this->rowsCount;
    }

    /**
     * Detect field separator
     *
     * @return string
     */
    private function detectSeparator() : string
    {
        $line = parent::readCurrentLine(true);

        $separators = [
            ',' => 0,
            ';' => 0,
            "\t" => 0,
        ];

        foreach ($separators as $separator => &$count) {
            $count = mb_substr_count($line, $separator, null);
        }

        return array_search(max($separators), $separators);
    }

    /**
     * Read columns
     *
     * @return array
     */
    private function readColumns() : array
    {
        $line = parent::readCurrentLine(true);

        return $this->lineToArray($line, false);
    }

    /**
     * Convert line to array
     *
     * @param string $line
     *
     * @throws CSVException
     *
     * @return array
     */
    private function lineToArray(string $line) : array
    {
        // line to array using separator
        //$columns = explode($this->separator, $line);
        $columns = str_getcsv($line, $this->separator, $this->enclosure, $this->escape);

        if (isset($this->columnsCount) && count($columns) !== $this->columnsCount) {
            $count = count($columns);
            throw new CSVException("columns count mismatch {$count} / {$this->columnsCount}");
        }

        // cleanup whitespace multibyte
        foreach ($columns as &$column) {
            $column = preg_replace('/^\\s+|\\s+$/u', '', $column);
        }

        if (isset($this->enclosure)) {
            foreach ($columns as &$column) {
                $column = preg_replace("/^{$this->enclosure}|{$this->enclosure}$/u", '', $column);
            }
        }

        if ($this->convertNumbers) {
            // convert numeric strings to numbers
            foreach ($columns as &$column) {
                if (!is_numeric($column)) {
                    continue;
                }

                if (filter_var($column, FILTER_VALIDATE_INT)) {
                    $column = (int) $column;
                } elseif (filter_var($column, FILTER_VALIDATE_FLOAT)) {
                    $column = (float) $column;
                }
            }
        }

        if (isset($this->columns) && $this->associativeArray) {
            // associative array
            return array_combine($this->columns, $columns);
        }

        return $columns;
    }

    /**
     * Detect enclosure
     *
     * @return string
     */
    private function detectEnclosure() : string
    {
        $line = parent::readCurrentLine(true);

        $enclosures = [
            '"' => 0,
            '\'' => 0,
        ];

        foreach ($enclosures as $enclosure => &$count) {
            $count = substr_count($line, $enclosure, 0, null);
        }

        if (max($enclosures) === 0) {
            return '';
        }

        return array_search(max($enclosures), $enclosures);

        /* alternate way
        $line = parent::readCurrentLine(true);

        $enclosures = [
            '"' => 0,
            '\'' => 0,
        ];

        foreach ($enclosures as $enclosure => &$count) {
            $count = mb_substr_count($line, $enclosure, null);
        }

        return array_search(max($enclosures), $enclosures);
        */

        /*
        $enclosures = [
            '"' => 0,
            '\'' => 0,
            '' => 0,
        ];

        foreach ($this->columns as $column) {
            foreach ($enclosures as $enclosure => &$count) {
                if (str_starts_with($column, $enclosure) && str_ends_with($column, $enclosure)) {
                    ++$count;
                }
            }
        }

        return array_search(max($enclosures), $enclosures);
        */
    }

    /**
     * Trim columns
     *
     * @return void
     */
    private function trimColumns() : void
    {
        foreach ($this->columns as &$column) {
            $column = preg_replace("/^{$this->enclosure}|{$this->enclosure}$/u", '', $column);
        }
    }

    /**
     * Detect if csv has a header
     *
     * @return bool
     */
    private function detectHeader() : bool
    {
        // look for keywords
        $keywords = [
            'name',
            'firstname',
            'lastname',
            'date',
            'year',
            'month',
            'day',
            'hour',
            'time',
            'time zone',
            'length',
            'size',
            'average',
            'description',
            'currency',
            'gross',
            'fee',
            'net',
            'balance',
            'type',
            'status',
            'title',
            'phone',
            'phone number',
            'start date',
            'end date',
        ];

        $keyword = 0;

        $columns = $this->readColumns();

        foreach ($columns as $column) {
            if (in_array(mb_strtolower($column), $keywords, true)) {
                ++$keyword;
            }
        }

        // look for numeric columns
        $numeric = 0;

        foreach ($columns as $column) {
            if (is_numeric($column)) {
                ++$numeric;
            }
        }

        // look for numeric columns in second row
        $row = $this->readRow(1);

        $numericSecond = 0;

        foreach ($row as $field) {
            if (is_numeric($field)) {
                ++$numericSecond;
            }
        }

        if ($numericSecond > $numeric) {
            return true;
        }

        return $keyword - $numeric > 0;
    }

    /**
     * Separator as string
     *
     * @return string
     */
    private function separator() : string
    {
        switch ($this->separator) {
            case "\t":
                return 'tab';

            default:
                return $this->separator;
        }
    }

    /**
     * Enclosure as string
     *
     * @return string
     */
    private function enclosure() : string
    {
        switch ($this->enclosure) {
            case '':
                return 'none';

            default:
                return $this->enclosure;
        }
    }
}
