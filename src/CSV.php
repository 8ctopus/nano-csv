<?php

namespace Oct8pus\CSV;

class CSV
{
    private $handle;

    private string $file;
    private int $size;
    private int $startOffset;

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
     * @return self
     */
    public function __construct()
    {
    }

    /**
     * Set file
     *
     * @param string $file
     *
     * @return self
     */
    public function setFile(string $file) : self
    {
        $this->file = $file;
        return $this;
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

        // set data start offset and encoding
        $this->startOffset = $this->bom->startOffset();
        $this->encoding = $this->bom->encoding();

        // seek to where data starts
        if (fseek($this->handle, $this->startOffset, SEEK_SET) !== 0) {
            throw new CSVException('fseek');
        }

        if (empty($this->encoding)) {
            $this->encoding = $this->detectEncoding();
        }

        $this->lineEnding = $this->detectLineEnding();
        $this->separator = $this->detectSeparator();

        $this->columns = $this->readColumns();
        $this->columnsCount = count($this->columns);

        $this->enclosure = $this->detectEnclosure();
        $this->cleanupColumns = $this->cleanupColumns();
        $this->header = $this->detectHeader();

        if (!$this->header) {
            // reset column names
            $this->columns = [];

            for ($i = 0; $i < $this->columnsCount; ++$i) {
                $this->columns[] = "column {$i}";
            }
        }

        return $this;
    }

    /**
     * Get byte order mark (bom)
     *
     * @return BOM
     */
    private function getBOM() : BOM
    {
        $data = str_split(fread($this->handle, $this->size > 3 ? 3 : $this->size));

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
     * @throws CSVException
     *
     * @return string
     */
    private function detectEncoding() : string
    {
        $line = $this->read($this->size > 500 ? 500 : $this->size, true);

        $encoding = mb_detect_encoding($line, ['auto'], true);

        if (!$encoding) {
            throw new CSVException('detect encoding');
        }

        return $encoding;
    }

    /**
     * Detect line ending
     *
     * @throws CSVException
     *
     * @return LineEnding
     */
    private function detectLineEnding() : LineEnding
    {
        $line = $this->read($this->size > 500 ? 500 : $this->size, true);

        // get line ending
        $endings = [
            LineEnding::Windows->toStr() => "\r\n",
            LineEnding::Linux->toStr() => "\n",
            LineEnding::Mac->toStr() => "\r",
        ];

        foreach ($endings as $name => $ending) {
            if (str_contains($line, $ending)) {
                return LineEnding::fromStr($name);
            }
        }

        throw new CSVException('detect line ending');
    }

    /**
     * Detect field separator
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
     * @return array
     */
    private function readColumns() : array
    {
        $line = $this->readLine(true);

        // line to array using separator
        $columns = explode($this->separator, $line);

        // cleanup whitespace multibyte
        foreach ($columns as &$column) {
            $column = preg_replace("/^\s+|\s+$/u", '', $column);
        }

        return $columns;
    }

    /**
     * Detect enclosure
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
        ];

        foreach ($this->columns as $column) {
            foreach ($enclosures as $enclosure) {
                if (str_starts_with($column, $enclosure)) {
                    ++$column;
                }

                if (str_ends_with($column, $enclosure)) {
                    ++$column;
                }
            }
        }

        return array_search(max($enclosures), $enclosures);
    }

    /**
     * Cleanup columns
     * @return void
     */
    private function cleanupColumns() : void
    {
        foreach ($this->columns as &$column) {
            $column = ltrim($column, $this->enclosure);
            $column = preg_replace("/^{$this->enclosure}|{$this->enclosure}$/u", '', $column);
        }
    }

    /**
     * Detect if file has a header
     * @return bool
     */
    private function detectHeader() : bool
    {
        foreach ($this->columns as $column) {
            if (is_numeric($column)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Read row
     *
     * @param int $row
     * @param bool $resetPosition
     *
     * @return array
     */
    public function readRow(int $row, bool $resetPosition) : array
    {
    }

    /**
     * Read from file
     *
     * @param  int    $size
     * @param  bool   $resetPosition
     *
     * @return string
     */
    private function read(int $size, bool $resetPosition) : string
    {
        // save position
        if ($resetPosition) {
            $position = ftell($this->handle);

            if ($position === false) {
                throw new CSVException('ftell');
            }
        }

        $str = fread($this->handle, $size);

        if ($str === false) {
            throw new CSVException('fread');
        }

        if (isset($position) && fseek($this->handle, $position, SEEK_SET) !== 0) {
            throw new CSVException('fseek');
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
     * Read line
     *
     * @param  bool $resetPosition
     *
     * @return string
     */
    private function readLine(bool $resetPosition) : string
    {
        $str = '';

        while (1) {
            $str .= $this->read(500, $resetPosition);

            $position = mb_strpos($str, $this->lineEnding->ending(), 0);

            if ($position) {
                return mb_substr($str, 0, $position);
            }
        }
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
}
