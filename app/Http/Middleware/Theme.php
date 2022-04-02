<?php

namespace App\Http\Middleware;

use Closure;
use Cookie;
use Illuminate\Http\Request;

class Theme
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $session = $request->session();
        $specificTheme = $request->input('theme');
        $cookie=null;

        if($specificTheme===null)
        {
            $specificTheme = $request->cookie('theme');
        }
        else
        {
            //reset
            if($specificTheme==='auto')
            {
                $cookie=Cookie::forget('theme');
                $specificTheme=null;
            }
            else
            {
                $cookie=Cookie::forever('theme',$specificTheme);
            }
        }


        //no theme or them switched asked
        if(!$session->exists('theme') || $session->get('theme')!==$specificTheme)
        {
            $session->put('theme',$specificTheme ?? $this->timestampToTheme(now()));
        }

        if($cookie!=null)
        {
            return $next($request)->withCookie($cookie);
        }
        return $next($request);

    }

    private function timestampToTheme(\DateTime $dateTime): string{
        $dayOfTheYear = $dateTime->format('z');
        $h24=$dateTime->format('G');
        $day = $dateTime->format('d');
        $month = $dateTime->format('m');
        if($h24>18 || $h24<7)
        {
            return 'dark';
        }
        if($day > 7 && $day<15 && $month==2)
        {
            return 'valentine';
        }
        if($day > 24 && $month==10)
        {
            return 'halloween';
        }
        if($dayOfTheYear < 80 || $dayOfTheYear > 356){
            return 'winter';
        }
        if($dayOfTheYear < 173){
            return 'fantasy';
        }
        if($dayOfTheYear < 266){
            return 'lemonade';
        }
        return 'autumn';
    }
}
