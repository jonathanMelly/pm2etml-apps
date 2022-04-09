<?php

//currently empty
if(!function_exists('ordinal'))
{
    function ordinal(int $number):string
    {
        if($number>31)
        {
            return '';
        }
        return preg_replace('/\d+/','',date("S", mktime(0, 0, 0, 0, $number, 0)));
    }
}
if(!function_exists('img'))
{
    function img(string $file):string
    {
        return '/dmz-assets/'.$file;
    }
}
if(!function_exists('allocationDetails'))
{
    function allocationDetails(\App\Models\JobDefinition $job):string
    {
        return $job->getAllocatedTime(\App\Enums\RequiredTimeUnit::HOUR).'h / '
            . $job->getAllocatedTime(\App\Enums\RequiredTimeUnit::PERIOD).'p';
    }
}

