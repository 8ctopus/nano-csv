<?php

namespace Oct8pus\CSV;

class CSV extends File
{
    private string $separator;
    private string $enclosure;
    private string $escape;
    private array $columns;
    private int $columnsCount;

    private bool $header;

    /**
     * Constructor
     *
     * @param string $file
     *
     * @return self
     */
    public function __construct(string $file)
    {
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
            "separator: {$this->separator}" . PHP_EOL .
            "enclosure: {$this->enclosure}" . PHP_EOL .
            "header: {$header}" . PHP_EOL .
            "columns ({$this->columnsCount}): {$columns}" . PHP_EOL;
    }

    /**
     * Autodetect csv properties
     *
     * @throws CSVException
     *
     * @return self
     */
    public function autoDetect() : self
    {
        parent::autoDetect();

        $this->separator = $this->detectSeparator();

        $this->columnsCount = $this->readColumnsCount();

        $this->header = $this->detectHeader();

        if ($this->header) {
            $this->columns = $this->readColumns();
            $this->enclosure = $this->detectEnclosure();
            $this->trimColumns();

            // skip header
            $this->readCurrentLine(false);
        } else {
            $this->columns = [];

            for ($i = 0; $i < $this->columnsCount; ++$i) {
                $this->columns[] = "column {$i}";
            }

            $this->enclosure = $this->detectEnclosure();
        }

        return $this;
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
            $row += 1;
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
            '\t' => 0,
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

        return $this->lineToArray($line);
    }

    /**
     * Convert line to array
     *
     * @param  string $line
     *
     * @throws CSVException
     *
     * @return array
     */
    private function lineToArray(string $line) : array
    {
        // line to array using separator
        $columns = explode($this->separator, $line);

        // cleanup whitespace multibyte
        foreach ($columns as &$column) {
            $column = preg_replace('/^\\s+|\\s+$/u', '', $column);
        }

        if (isset($this->enclosure)) {
            foreach ($columns as &$column) {
                $column = preg_replace("/^{$this->enclosure}|{$this->enclosure}$/u", '', $column);
            }
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

        $enclosures = [
            '"' => 0,
            '\'' => 0,
            ''  => 0,
        ];

        foreach ($this->columns as $column) {
            foreach ($enclosures as $enclosure => &$count) {
                if (str_starts_with($column, $enclosure) && str_ends_with($column, $enclosure)) {
                    ++$count;
                }
            }
        }

        return array_search(max($enclosures), $enclosures);
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
            'year',
            'month',
            'day',
            'hour',
            'time',
            'length',
            'size',
            'average',
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

    private function readColumnsCount() : int
    {
        return count($this->readColumns());
    }
}
