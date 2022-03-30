<?php

namespace App\Http\Middleware;

use Closure;
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
        $theme = $request->input('theme');

        if($theme!==null || !$session->exists('theme'))
        {
            if($theme=='reset')
            {
                $theme=null;
            }
            //set default theme to current season
            $session->put('theme',$theme??$this->timestampToTheme(now()));
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
