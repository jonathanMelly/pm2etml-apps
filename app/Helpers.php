<?php

use App\Models\Attachment;
use Carbon\Carbon;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Facades\Crypt;

if (! function_exists('ordinal')) {
    function ordinal(int $number): string
    {
        if ($number > 31) {
            return '';
        }

        return preg_replace('/\d+/', '', date('S', mktime(0, 0, 0, 0, $number, 0)));
    }
}

if (! function_exists('uploadDisk')) {
    function uploadDisk(): \Illuminate\Contracts\Filesystem\Filesystem|\Illuminate\Filesystem\FilesystemAdapter
    {
        return Storage::disk(\App\Constants\DiskNames::UPLOAD);
    }
}

if (! function_exists('attachmentPathInUploadDisk')) {
    function attachmentPathInUploadDisk(?string $file = null, bool $temporary = false, bool $deleted = false): string
    {
        $parts = [];
        if ($temporary) {
            $parts[] = \App\Constants\FileFormat::ATTACHMENT_TEMPORARY_SUBFOLDER;
        }
        if ($deleted) {
            $parts[] = \App\Constants\FileFormat::ATTACHMENT_DELETED_SUBFOLDER;
        }
        if ($file != null) {
            $parts[] = $file;
        }

        return implode(DIRECTORY_SEPARATOR, $parts);
    }
}

if (! function_exists('attachmentUri')) {
    function attachmentUri(Attachment $attachment): string
    {
        return route('dmz-asset', ['file' => $attachment->storage_path,'name' => Crypt::encryptString($attachment->name)]);
    }
}

if (! function_exists('tbl')) {
    function tbl($class)
    {
        //TODO add cache ?
        return app($class)->getTable();
    }
}

if (! function_exists('b2s')) {
    function b2s(mixed $boolValue): string
    {
        if (! is_bool($boolValue)) {
            $boolValue = filter_var($boolValue, FILTER_VALIDATE_BOOLEAN);

        }

        return $boolValue ? 'true' : 'false';
    }
}

if (! function_exists('existsAndNotEmpty')) {
    function existsAndNotEmpty(ArrayAccess $array, mixed $key)
    {
        if (isset($array[$key]) && ($value = trim($array[$key])) !== '') {
            return $value;
        }

        return null;
    }
}

if (! function_exists('stringNullOrEmpty')) {
    function stringNullOrEmpty(?string $str): bool
    {
        return $str === null || trim($str) === '';
    }
}

if (! function_exists('troolHtml')) {
    function troolHtml(?bool $value): string
    {
        if ($value === null) {
            return 'n/a';
        }

        return spanColor($value ? 'green' : 'red', $value ? 'Ok' : 'Ko');
    }
}

if (! function_exists('gradeHtml')) {
    /**
     * Convert evaluation result (na/pa/a/la or old boolean 0/1) to HTML with color
     */
    function gradeHtml($value): string
    {
        if ($value === null) {
            return 'n/a';
        }

        // Handle old boolean values (backward compatibility)
        if ($value === '1' || $value === 1 || $value === true) {
            return spanColor('green', 'A');
        }
        if ($value === '0' || $value === 0 || $value === false) {
            return spanColor('red', 'NA');
        }

        // Handle new grade values
        return match($value) {
            'la' => spanColor('green', 'LA'),
            'a' => spanColor('green', 'A'),
            'pa' => spanColor('orange', 'PA'),
            'na' => spanColor('red', 'NA'),
            default => 'n/a'
        };
    }
}

if (! function_exists('spanColor')) {
    function spanColor(string $color, string $content): string
    {
        return '<span style="color:'.$color.'">'.$content.'</span>';
    }
}

if (! function_exists('mdSmall')) {
    function mdSmall(?string $str, bool $parenthesis = false): string
    {
        if ($str == null) {
            return '';
        }

        return '<sup><sub>'.($parenthesis ? '(' : '').$str.($parenthesis ? ')' : '').'</sub></sup>';
    }
}

if (! function_exists('diff')) {
    function diff(mixed $a, mixed $b): string
    {
        if ($a == $b) {
            return $a;
        }

        return $a.' => '.$b;
    }
}

if (! function_exists('df')) {
    function df(?Carbon $date, string $format = 'd.m.Y H:i:s'): string
    {
        if ($date === null) {
            return '';
        }

        return date_format($date, $format);
    }
}

if (! function_exists('sso')) {
    function sso(): Laravel\Socialite\Contracts\Provider
    {
        return Socialite::driver('azure');
    }
}

if (! function_exists('safeJsString')) {

    function safeJsString(string $value, $stringDelimiter = "'"): string
    {
        return str_replace($stringDelimiter, '\\'.$stringDelimiter, $value);
    }
}
