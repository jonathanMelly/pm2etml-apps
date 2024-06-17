<?php

namespace App\Http\Middleware;

use App\Models\AcademicPeriod;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class AcademicPeriodFilter
{
    const ACADEMIC_PERIOD_ID_REQUEST_PARAM = 'academicPeriodId';

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next): Response
    {
        //Validate pre-existing data
        $reset = true;
        if ($request->has(self::ACADEMIC_PERIOD_ID_REQUEST_PARAM)) {
            $current = $request->get(self::ACADEMIC_PERIOD_ID_REQUEST_PARAM);

            if (! AcademicPeriod::whereId($current)->exists()) {
                Log::warning('Invalid period id '.$current.' => will be replaced');
            } else {
                $reset = false;
            }
        }

        if ($reset) {
            $request->merge([self::ACADEMIC_PERIOD_ID_REQUEST_PARAM => \App\Models\AcademicPeriod::current()]);
        }

        return $next($request);
    }
}
