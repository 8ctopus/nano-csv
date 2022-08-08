<?php

namespace Oct8pus\CSV;

class CSV
{
    private $handle;

    private string $file;
    private int $size;
    private int $startOffset;
    private int $currentOffset;

    private BOM $bom;
    private LineEnding $lineEnding;

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
        if (!file_exists($file)) {
            throw new CSVException('file does not exist');
        }

        $this->file = $file;

        $this->currentOffset = 0;
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
            "file: {$this->file}" . PHP_EOL .
            "size: {$this->size}" . PHP_EOL .
            "BOM: {$this->bom->debug()}" . PHP_EOL .
            "encoding: {$this->encoding}" . PHP_EOL .
            "line ending: {$this->lineEnding->toStr()}" . PHP_EOL .
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
        // open file
        $this->handle = fopen($this->file, 'r', false, null);

        if ($this->handle === false) {
            throw new CSVException('open file');
        }

        // get file info
        $stat = fstat($this->handle);

        if ($stat === false) {
            throw new CSVException('file stat');
        }

        // get size
        $this->size = $stat['size'];

        if ($this->size === 0) {
            throw new CSVException('empty file');
        }

        // get bom
        $this->bom = $this->getBOM();

        // set data start offset
        $this->startOffset = $this->bom->startOffset();
        $this->currentOffset = $this->startOffset;

        // seek to where data starts
        if (fseek($this->handle, $this->currentOffset, SEEK_SET) !== 0) {
            throw new CSVException('fseek');
        }

        // set encoding
        $this->encoding = $this->bom->encoding();

        // read part of file
        $text = $this->read($this->size > 500 ? 500 : $this->size, true);

        if (empty($this->encoding)) {
            $this->encoding = $this->detectEncoding($text);
        }

        $this->lineEnding = $this->detectLineEnding($text);

        $this->separator = $this->detectSeparator();

        $this->columnsCount = $this->readColumnsCount();

        $this->header = $this->detectHeader();

        if ($this->header) {
            $this->columns = $this->readColumns();
            $this->enclosure = $this->detectEnclosure();
            $this->trimColumns();
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
        // save offset
        $offset = $this->currentOffset;

        if (fseek($this->handle, $this->startOffset, SEEK_SET) !== 0) {
            throw new CSVException('fseek');
        }

        if (isset($this->header) && $this->header) {
            $row += 1;
        }

        for ($i = 0; $i <= $row; ++$i) {
            $line = $this->readLine(false);
        }

        if (isset($offset)) {
            if (fseek($this->handle, $offset, SEEK_SET) !== 0) {
                throw new CSVException('fseek');
            }

            $this->currentOffset = $offset;
        }

        return $this->lineToArray($line);
    }

    /**
     * Read next row
     *
     * @return ?array
     */
    public function readNextRow() : ?array
    {
        $line = $this->readLine(false);

        if (!$line) {
            return null;
        }

        return $this->lineToArray($line);
    }

    /**
     * Read line
     *
     * @param bool $resetOffset
     *
     * @return ?string
     */
    private function readLine(bool $resetOffset) : ?string
    {
        if ($this->currentOffset >= $this->size) {
            return null;
        }

        $offset = $this->currentOffset;

        $str = '';
        $length = 100;
        $read = 0;
        $end = false;

        while (1) {
            // check for end of file
            if ($read + $length + $offset > $this->size) {
                $length = $this->size - $offset - $read;
                $end = true;
            }

            $str .= $this->read($length, false);
            $read += $length;

            $position = mb_strpos($str, $this->lineEnding->ending(), 0);

            if ($position !== false || $end) {
                $line = mb_substr($str, 0, $end ? null : $position);

                if ($resetOffset) {
                    $this->currentOffset = $offset;
                } else {
                    $this->currentOffset = $offset + strlen(mb_convert_encoding($line, $this->encoding, 'UTF-8')) + strlen(mb_convert_encoding($this->lineEnding->ending(), $this->encoding, 'UTF-8'));
                }

                if (fseek($this->handle, $this->currentOffset, SEEK_SET) !== 0) {
                    throw new CSVException('fseek');
                }

                return $line;
            }
        }

        throw new CSVException();
    }

    /**
     * Read from file
     *
     * @param int  $length
     * @param bool $resetOffset
     *
     * @return string
     */
    private function read(int $length, bool $resetOffset) : string
    {
        if ($length <= 0) {
            throw new CSVException('invalid length');
        }

        // save offset
        if ($resetOffset) {
            $offset = $this->currentOffset;
        }

        if ($this->currentOffset + $length > $this->size) {
            $pos = $this->currentOffset + $length;
            throw new CSVException("out of bounds {$pos} / {$this->size}");
        }

        $str = fread($this->handle, $length);

        if ($str === false) {
            throw new CSVException('fread');
        }

        if (isset($offset)) {
            if (fseek($this->handle, $offset, SEEK_SET) !== 0) {
                throw new CSVException('fseek');
            }

            $this->currentOffset = $offset;
        } else {
            $this->currentOffset += $length;
        }

        if (!empty($this->encoding)) {
            $str = mb_convert_encoding($str, 'UTF-8', $this->encoding);
        }

        if ($str === false) {
            throw new CSVException('convert encoding');
        }

        return $str;
    }

    /**
     * Get byte order mark (bom)
     *
     * @return BOM
     */
    private function getBOM() : BOM
    {
        $data = str_split($this->read($this->size > 3 ? 3 : $this->size, true));

        $boms = [
            BOM::Utf8->encoding() => [0xEF, 0xBB, 0xBF],
            BOM::Utf16LE->encoding() => [0xFF, 0xFE],
            BOM::Utf16BE->encoding() => [0xFE, 0xFF],
        ];

        foreach ($boms as $name => $bom) {
            for ($i = 0; $i < sizeof($bom); ++$i) {
                if ($bom[$i] !== ord($data[$i])) {
                    break;
                }

                return BOM::fromStr($name);
            }
        }

        return BOM::None;
    }

    /**
     * Detect encoding
     *
     * @param string $text
     *
     * @throws CSVException
     *
     * @return string
     */
    private function detectEncoding(string $text) : string
    {
        $encoding = mb_detect_encoding($text, ['auto'], true);

        if (!$encoding) {
            throw new CSVException('detect encoding');
        }

        return $encoding;
    }

    /**
     * Detect line ending
     *
     * @param string $text
     *
     * @throws CSVException
     *
     * @return LineEnding
     */
    private function detectLineEnding(string $text) : LineEnding
    {
        $endings = [
            LineEnding::Windows->toStr() => "\r\n",
            LineEnding::Linux->toStr() => "\n",
            LineEnding::Mac->toStr() => "\r",
        ];

        foreach ($endings as $name => $ending) {
            if (str_contains($text, $ending)) {
                return LineEnding::fromStr($name);
            }
        }

        throw new CSVException('detect line ending');
    }

    /**
     * Detect field separator
     *
     * @return string
     */
    private function detectSeparator() : string
    {
        $line = $this->readLine(true);

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
        $line = $this->readLine(true);

        return $this->lineToArray($line);
    }

    /**
     * Convert line to array
     *
     * @param  string $line
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
        $line = $this->readLine(true);

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
