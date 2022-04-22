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

if(!function_exists('tbl'))
{
    function tbl($class)
    {
        return app($class)->getTable();
    }
}

