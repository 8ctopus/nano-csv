<?php

namespace Oct8pus\CSV;

enum BOM
{
    case None;
    case Utf8;
    case Utf16LE;
    case Utf16BE;

    /**
     * from string
     * @param  string $str
     * @return self
     */
    public static function fromStr(string $str) : self
    {
        switch (strtolower($str)) {
            case 'none':
                return self::None;

            case 'utf8':
            case 'utf-8':
                return self::Utf8;

            case 'utf16le':
            case 'utf-16le':
                return self::Utf16LE;

            case 'utf16be':
            case 'utf-16be':
                return self::Utf16BE;

            default:
                throw new CSVException('unknown BOM');
        }
    }

    public function startOffset() : int
    {
        return match ($this) {
            self::None => 0,
            self::Utf8 => 3,
            self::Utf16LE => 2,
            self::Utf16BE => 2,
        };
    }

    public function encoding() : string
    {
        return match ($this) {
            self::None => '',
            self::Utf8 => 'UTF-8',
            self::Utf16LE => 'UTF-16LE',
            self::Utf16BE => 'UTF-16BE',
        };
    }

    public function debug() : string
    {
        return match ($this) {
            self::None => 'None',
            self::Utf8 => 'UTF-8',
            self::Utf16LE => 'UTF-16LE',
            self::Utf16BE => 'UTF-16BE',
        };
    }
}
