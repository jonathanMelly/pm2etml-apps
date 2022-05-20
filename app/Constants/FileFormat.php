<?php

namespace App\Constants;

class FileFormat
{
    public const IMAGE_FORMATS=['jpeg','jpg','svg','png','tiff','bmp'];
    public static function getImageFormatsAsRegex(): string
    {
        return implode('|', self::IMAGE_FORMATS);
    }

    public static function getImageFormatsAsCSV(): string
    {
        return implode(',', self::IMAGE_FORMATS);
    }
}
