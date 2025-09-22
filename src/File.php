<?php

declare(strict_types=1);

namespace Oct8pus\CSV;

class File
{
    protected int $startOffset;
    protected int $currentOffset;

    private string $file;

    /**
     * @var resource
     */
    private $handle;
    private int $size;
    private BOM $bom;
    private string $encoding;
    private LineEnding $lineEnding;
    private int $linesCount;

    /**
     * Constructor
     *
     * @param string $file
     *
     * @return self
     *
     * @throws FileException
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
            "file: {$this->file}" . PHP_EOL
            . "size: {$this->size}" . PHP_EOL
            . "BOM: {$this->bom->toStr()}" . PHP_EOL
            . "encoding: {$this->encoding}" . PHP_EOL
            . "line ending: {$this->lineEnding->toStr()}" . PHP_EOL
            . "lines count: {$this->linesCount()}" . PHP_EOL;
    }

    /**
     * Get/set property
     *
     * @param string       $method
     * @param array<mixed> $args
     *
     * @return null|mixed
     */
    public function __call(string $method, array $args) : mixed
    {
        $operation = substr($method, 0, 3);

        $property = str_replace(['get', 'set'], '', $method);
        $property = lcfirst($property);

        switch ($operation) {
            case 'get':
                return $this->{$property};

            case 'set':
                throw new FileException('nothing can be set');

            default:
                throw new FileException("unknown property {$property}");
        }
    }

    /**
     * Autodetect file properties
     *
     * @return self
     *
     * @throws FileException
     */
    public function autoDetect() : self
    {
        // open file
        $handle = fopen($this->file, 'r', false, null);

        if ($handle === false) {
            throw new FileException('open file');
        }

        $this->handle = $handle;
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
        $this->bom = BOM::get($this->read($this->size > 3 ? 3 : $this->size, true));

        // set data start offset
        $this->startOffset = $this->bom->startOffset();
        $this->currentOffset = $this->startOffset;

        // seek to where data starts
        $this->seek($this->currentOffset);

        // set encoding
        $this->encoding = $this->bom->encoding();

        // read part of file
        $length = $this->size > 1000 ? 1000 : $this->size;
        $text = $this->read($length - $this->startOffset, true);

        if (empty($this->encoding)) {
            $this->encoding = $this->detectEncoding($text);
        }

        $this->lineEnding = LineEnding::detect($text, $this->encoding);

        return $this;
    }

    /**
     * Read line
     *
     * @param int $line
     *
     * @return string
     *
     * @throws FileException
     */
    public function readLine(int $line) : string
    {
        if (isset($this->linesCount) && $line >= $this->linesCount) {
            throw new FileException("out of bounds {$line} / {$this->linesCount}");
        }

        // save offset
        $offset = $this->currentOffset;

        // seek to data start
        $this->seek($this->startOffset);

        for ($i = 0; $i <= $line; ++$i) {
            $str = $this->readCurrentLine(false);
        }

        // seek back to saved offset
        $this->seek($offset);

        if (!isset($str)) {
            throw new FileException('$str not set');
        }

        return $str;
    }

    /**
     * Read current line
     *
     * @param bool $resetOffset
     *
     * @return ?string
     *
     * @throws FileException
     */
    public function readCurrentLine(bool $resetOffset) : ?string
    {
        if ($this->currentOffset >= $this->size) {
            return null;
        }

        // save current offset
        $offset = $this->currentOffset;

        $str = '';
        $length = 100;
        $read = 0;

        $potentialEnd = false;
        $end = false;

        while (1) {
            // check for end of file
            if ($read + $length + $offset >= $this->size) {
                $length = $this->size - $offset - $read;
                $potentialEnd = true;
            }

            $str .= $this->read($length, false);
            $read += $length;

            $position = strpos($str, $this->lineEnding->ending($this->encoding), 1);

            if ($potentialEnd && $position === false) {
                $end = true;
            }

            if ($position !== false || $end) {
                $line = substr($str, 0, $end ? $read : $position);

                if ($resetOffset) {
                    $this->currentOffset = $offset;
                } else {
                    if (!$end) {
                        $this->currentOffset = $offset + $position;
                    } else {
                        $this->currentOffset = $offset + $read;
                    }
                }

                $this->seek($this->currentOffset);

                return trim(mb_convert_encoding($line, 'UTF-8', $this->encoding), "\r\n");
            }
        }
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
     * Read lines count
     *
     * @return int
     */
    public function linesCount() : int
    {
        // get cached value
        if (isset($this->linesCount)) {
            return $this->linesCount;
        }

        // save offset
        $offset = $this->currentOffset;

        // seek to data start
        $this->seek($this->startOffset);

        $linesCount = 0;

        while ($this->readNextLine() !== null) {
            ++$linesCount;
        }

        $this->linesCount = $linesCount;

        // seek back to saved offset
        $this->seek($offset);

        return $this->linesCount;
    }

    /**
     * Seek
     *
     * @param int $offset
     *
     * @return void
     *
     * @throws FileException
     */
    protected function seek(int $offset) : void
    {
        if (fseek($this->handle, $offset, SEEK_SET) !== 0) {
            throw new FileException();
        }

        $this->currentOffset = $offset;
    }

    /**
     * Read from file
     *
     * @param int  $length
     * @param bool $resetOffset
     *
     * @return string
     *
     * @throws FileException
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
            $this->seek($offset);
        } else {
            $this->currentOffset += $length;
        }

        return $str;
    }

    /**
     * Detect encoding
     *
     * @param string $text
     *
     * @return string
     *
     * @throws FileException
     */
    private function detectEncoding(string $text) : string
    {
        $encoding = mb_detect_encoding($text, ['auto', 'UTF-8', 'Windows-1252', 'ISO-8859-1'], true);

        if (!$encoding) {
            throw new FileException('detect encoding');
        }

        return $encoding;
    }
}
