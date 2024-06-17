<?php

namespace App\Http\Middleware;

use App\Enums\RequiredTimeUnit;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class TimeUnitFilter
{
    const TIME_UNIT_REQUEST_KEY = 'timeUnit';

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next): Response
    {
        $reset = true;
        if ($request->has(self::TIME_UNIT_REQUEST_KEY)) {
            $current = $request->get(self::TIME_UNIT_REQUEST_KEY);
            $valid = RequiredTimeUnit::tryFrom($current);

            if (! $valid) {
                Log::warning('Wrong timeunit in request: '.$current.' => it will be replaced');
            } else {
                $reset = false;
            }
        }

        if ($reset) {
            $request->merge([self::TIME_UNIT_REQUEST_KEY => RequiredTimeUnit::PERIOD->value]);
        }

        return $next($request);
    }
}
