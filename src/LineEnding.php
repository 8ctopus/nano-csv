<?php

namespace Oct8pus\CSV;

enum LineEnding
{
    case Linux;
    case Windows;
    case Mac;

    /**
     * from string
     * @param  string $str
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
     * to string
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
}
