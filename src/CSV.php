<?php

namespace Oct8pus\CSV;

class CSV
{
    private $handle;

    private string $file;
    private int $size;

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
        $handle = fopen($this->file, 'r', false, null);

        if ($handle === false) {
            throw new CSVException('open file');
        }

        // get file info
        $stat = fstat($handle);

        if ($stat === false) {
            throw new CSVException('file stat');
        }

        // get size
        $this->size = $stat['size'];

        // get bom
        $this->bom = $this->bom($handle);

        $this->lineEnding = $this->lineEnding($handle);

        return $this;
    }

    /**
     * Check for byte order mark (bom)
     *
     * @param  resource $handle
     *
     * @return BOM
     */
    private function bom($handle) : BOM
    {
        $data = str_split(fread($handle, 3));

        // seek back to zero
        if (fseek($handle, 0, SEEK_SET) !== 0) {
            throw new CSVException('fseek');
        }

        $boms = [
            BOM::Utf8->toStr() => [0xEF, 0xBB, 0xBF],
            BOM::Utf16LE->toStr() => [0xFF, 0xFE],
            BOM::Utf16BE->toStr() => [0xFE, 0xFF],
        ];

        foreach ($boms as $name => $bom) {
            for ($i = 0; $i < sizeof($bom); ++$i) {
                if ($bom[$i] !== $data[$i]) {
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
     * @param resource $handle
     *
     * @throws CSVException
     *
     * @return LineEnding
     */
    private function lineEnding($handle) : LineEnding
    {
        $position = ftell($handle);

        if ($position === false) {
            throw new CSVException('ftell');
        }

        $line = fread($handle, $this->size > 500 ? 500 : $this->size);

        if (fseek($handle, $position, SEEK_SET) !== 0) {
            throw new CSVException('fseek');
        }

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
            "bom: {$this->bom->toStr()}" . PHP_EOL .
            "ending: {$this->lineEnding->toStr()}" . PHP_EOL;
    }
}
