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

    /**
     * Constructor
     *
     * @return self
     */
    public function __construct()
    {
    }

    public function setFile(string $file) : self
    {
        $this->file = $file;
        return $this;
    }

    /**
     * Autodetect
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
        $this->bom = $this->bom();

        // set data start offset and encoding
        switch ($this->bom) {
            case BOM::Utf8:
                $this->startOffset = 3;
                $this->encoding = 'UTF-8';
                break;

            case BOM::Utf16LE:
                $this->startOffset = 2;
                $this->encoding = 'UTF-16LE';
                break;

            case BOM::Utf16BE:
                $this->startOffset = 2;
                $this->encoding = 'UTF-16BE';
                break;

            default:
                $this->startOffset = 0;
                $this->encoding = '';
                break;
        }

        // seek to where data starts
        if (fseek($this->handle, $this->startOffset, SEEK_SET) !== 0) {
            throw new CSVException('fseek');
        }

        // get line ending
        $this->lineEnding = $this->lineEnding();

        return $this;
    }

    /**
     * Check for byte order mark (bom)
     *
     * @return BOM
     */
    private function bom() : BOM
    {
        $data = str_split(fread($this->handle, $this->size > 3 ? 3 : $this->size));

        $boms = [
            BOM::Utf8->toStr() => [0xEF, 0xBB, 0xBF],
            BOM::Utf16LE->toStr() => [0xFF, 0xFE],
            BOM::Utf16BE->toStr() => [0xFE, 0xFF],
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
     * Get line ending
     *
     * @throws CSVException
     *
     * @return LineEnding
     */
    private function lineEnding() : LineEnding
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

        throw new CSVException('line ending');
    }

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
     * Debug
     *
     * @return string
     */
    public function __toString() : string
    {
        return
            "file: {$this->file}" . PHP_EOL .
            "size: {$this->size}" . PHP_EOL .
            "BOM: {$this->bom->toStr()}" . PHP_EOL .
            "line ending: {$this->lineEnding->toStr()}" . PHP_EOL;
    }
}
