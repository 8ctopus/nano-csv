<?php

declare(strict_types=1);

namespace Oct8pus\CSV;

class CSV extends File
{
    /**
     * @var array<int, mixed>
     */
    protected array $columns;
    private string $separator;
    private string $enclosure;
    private string $escape;

    private bool $header;
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
        $this->escape = '\\';

        $extension = pathinfo($file, PATHINFO_EXTENSION);

        if ($extension !== 'csv') {
            throw new CSVException("invalid extension {$extension}");
        }

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
            "separator: {$this->separator()}" . PHP_EOL .
            "enclosure: {$this->enclosure()}" . PHP_EOL .
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
    public function __call(string $method, array $args) : mixed
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

                // no break
            case 'set':
                $type = gettype($this->{$property});

                if ($type !== gettype($args[0])) {
                    throw new CSVException("value {$args[0]} must be of type {$type}");
                }

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

                // no break
            default:
                throw new CSVException("unknown operation {$operation}");
        }
    }

    /**
     * Autodetect csv properties
     *
     * @return self
     *
     * @throws CSVException
     */
    public function autoDetect() : self
    {
        parent::autoDetect();

        $this->separator = $this->detectSeparator();
        $this->enclosure = $this->detectEnclosure();

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
     * Read row
     *
     * @param int $row
     *
     * @return array<int, mixed>
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
     * @return ?array<int, mixed>
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
        if (isset($this->rowsCount)) {
            return $this->rowsCount;
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

        if ($line === null) {
            throw new CSVException('detect separator');
        }

        $separators = [
            ',' => 0,
            ';' => 0,
            "\t" => 0,
        ];

        foreach ($separators as $separator => &$count) {
            $count = mb_substr_count($line, $separator, null);
        }

        return array_search(max($separators), $separators, true);
    }

    /**
     * Read columns
     *
     * @return array<int, mixed>
     */
    private function readColumns() : array
    {
        $line = parent::readCurrentLine(true);

        if ($line === null) {
            throw new CSVException('no columns found');
        }

        return $this->lineToArray($line);
    }

    /**
     * Convert line to array
     *
     * @param string $line
     *
     * @return array<int, mixed>
     *
     * @throws CSVException
     */
    private function lineToArray(string $line) : array
    {
        // line to array using separator
        //$columns = explode($this->separator, $line);
        // https://github.com/php/php-src/issues/16931
        $columns = str_getcsv($line, $this->separator, !empty($this->enclosure) ? $this->enclosure : '"', $this->escape);

        if (isset($this->columnsCount) && count($columns) !== $this->columnsCount) {
            $count = count($columns);
            throw new CSVException("columns count mismatch - {$count} / {$this->columnsCount}");
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

                // check if int
                if (filter_var($column, FILTER_VALIDATE_INT)) {
                    $column = (int) $column;
                    continue;
                }

                // check if float
                // FIX ME https://github.com/php/php-src/pull/9338 FILTER_FLAG_DISALLOW_SCIENTIFIC
                if (filter_var($column, FILTER_VALIDATE_FLOAT)) {
                    $column = (float) $column;
                    continue;
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

        if ($line === null) {
            throw new CSVException('detect enclosure');
        }

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

        return array_search(max($enclosures), $enclosures, true);
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
