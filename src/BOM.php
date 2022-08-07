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

    /**
     * to string
     * @return string
     */
    public function toStr() : string
    {
        return match ($this) {
            self::None => 'None',
            self::Utf8 => 'UTF-8',
            self::Utf16LE => 'UTF-16LE',
            self::Utf16BE => 'UTF-16BE',
        };
    }
}
