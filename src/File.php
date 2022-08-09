<?php

namespace Oct8pus\CSV;

class File
{
    private $handle;

    private string $file;

    private int $size;
    private int $startOffset;
    private int $currentOffset;

    private BOM $bom;
    private string $encoding;
    private LineEnding $lineEnding;

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
            throw new FileException('file does not exist');
        }

        $this->file = $file;
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
            "BOM: {$this->bom->debug()}" . PHP_EOL .
            "encoding: {$this->encoding}" . PHP_EOL .
            "line ending: {$this->lineEnding->toStr()}" . PHP_EOL;
    }

    /**
     * Autodetect file properties
     *
     * @throws FileException
     *
     * @return self
     */
    public function autoDetect() : self
    {
        // open file
        $this->handle = fopen($this->file, 'r', false, null);

        if ($this->handle === false) {
            throw new FileException('open file');
        }

        $this->currentOffset = 0;

        // get file info
        $stat = fstat($this->handle);

        if ($stat === false) {
            throw new FileException('file stat');
        }

        // get size
        $this->size = $stat['size'];

        if ($this->size === 0) {
            throw new FileException('empty file');
        }

        // get bom
        $this->bom = $this->getBOM();

        // set data start offset
        $this->startOffset = $this->bom->startOffset();
        $this->currentOffset = $this->startOffset;

        // seek to where data starts
        if (fseek($this->handle, $this->currentOffset, SEEK_SET) !== 0) {
            throw new FileException('fseek');
        }

        // set encoding
        $this->encoding = $this->bom->encoding();

        // read part of file
        $text = $this->read($this->size > 500 ? 500 : $this->size, true);

        if (empty($this->encoding)) {
            $this->encoding = $this->detectEncoding($text);
        }

        $this->lineEnding = LineEnding::detect($text, $this->encoding);

        return $this;
    }

    /**
     * Read line
     *
     * @param int $number
     *
     * @return string
     */
    public function readLine(int $number) : string
    {
        // save offset
        $offset = $this->currentOffset;

        if (fseek($this->handle, $this->startOffset, SEEK_SET) !== 0) {
            throw new FileException('fseek');
        }

        for ($i = 0; $i <= $number; ++$i) {
            $line = $this->readCurrentLine(false);
        }

        if (fseek($this->handle, $offset, SEEK_SET) !== 0) {
            throw new FileException('fseek');
        }

        $this->currentOffset = $offset;

        return $line;
    }

    /**
     * Read current line
     *
     * @param bool $resetOffset
     *
     * @return ?string
     */
    public function readCurrentLine(bool $resetOffset) : ?string
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

            $position = strpos($str, $this->lineEnding->ending($this->encoding), 0);

            if ($position !== false || $end) {
                $line = mb_substr($str, 0, $end ? null : $position);

                if ($resetOffset) {
                    $this->currentOffset = $offset;
                } else {
                    $this->currentOffset = $offset + $position + $this->lineEnding->length($this->encoding);
                }

                if (fseek($this->handle, $this->currentOffset, SEEK_SET) !== 0) {
                    throw new FileException('fseek');
                }

                return mb_convert_encoding($line, 'UTF-8', $this->encoding);
            }
        }

        throw new FileException();
    }

    /**
     * Read next line
     *
     * @return ?string
     */
    public function readNextLine() : ?string
    {
        return $this->readCurrentLine(false);
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
            throw new FileException('invalid length');
        }

        // save offset
        if ($resetOffset) {
            $offset = $this->currentOffset;
        }

        if ($this->currentOffset + $length > $this->size) {
            $position = $this->currentOffset + $length;
            throw new FileException("out of bounds {$position} / {$this->size}");
        }

        $str = fread($this->handle, $length);

        if ($str === false) {
            throw new FileException('fread');
        }

        if (isset($offset)) {
            if (fseek($this->handle, $offset, SEEK_SET) !== 0) {
                throw new FileException('fseek');
            }

            $this->currentOffset = $offset;
        } else {
            $this->currentOffset += $length;
        }

/*
        if (!empty($this->encoding)) {
            $str = mb_convert_encoding($str, 'UTF-8', $this->encoding);
        }
*/

        if ($str === false) {
            throw new FileException('convert encoding');
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
        $data = $this->read($this->size > 3 ? 3 : $this->size, true);

        return BOM::get($data);
    }

    /**
     * Detect encoding
     *
     * @param string $text
     *
     * @throws FileException
     *
     * @return string
     */
    private function detectEncoding(string $text) : string
    {
        $encoding = mb_detect_encoding($text, ['auto'], true);

        if (!$encoding) {
            throw new FileException('detect encoding');
        }

        return $encoding;
    }
}
