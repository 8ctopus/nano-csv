<?php

namespace Oct8pus\CSV;

enum LineEnding
{
    case Windows;
    case Linux;
    case Mac;

    /**
     * From string
     *
     * @param string $str
     *
     * @return self
     */
    public static function fromStr(string $str) : self
    {
        switch (strtolower($str)) {
            case 'linux':
                return self::Linux;

            case 'windows':
                return self::Windows;

            case 'mac':
                return self::Mac;

            default:
                throw new CSVException('unknown line ending');
        }
    }

    /**
     * To string
     *
     * @return string
     */
    public function toStr() : string
    {
        return match ($this) {
            self::Linux => 'Linux',
            self::Windows => 'Windows',
            self::Mac => 'Mac',
        };
    }

    public static function detect(string $text, string $encoding) : self
    {
        foreach (self::cases() as $case) {
            if (str_contains($text, $case->ending($encoding))) {
                return $case;
            }
        }
    }

    public function ending(string $encoding) : string
    {
        $ending = match ($this) {
            self::Linux => "\n",
            self::Windows => "\r\n",
            self::Mac => "\r",
        };

        return mb_convert_encoding($ending, $encoding, 'ASCII');
    }

    public function length(string $encoding) : int
    {
        if (in_array(strtoupper($encoding), ['UTF-16BE', 'UTF-16LE'], true)) {
            return match ($this) {
                self::Linux => 2,
                self::Windows => 4,
                self::Mac => 2,
            };
        }

        return match ($this) {
            self::Linux => 1,
            self::Windows => 2,
            self::Mac => 1,
        };
    }
}
