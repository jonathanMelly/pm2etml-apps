<?php

namespace App;

use Carbon\Carbon;

class DateFormat
{
    const ECHARTS_FORMAT = 'Y-m-d h:i';

    const HTML_FORMAT = 'Y-m-d';

    public static function DateFromHtmlInput(string $date): Carbon
    {
        return Carbon::createFromFormat(self::HTML_FORMAT, $date);
    }
}
