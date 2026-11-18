<?php

namespace App\Http\Middleware;

use App\Support\Locales;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/** Applies the visitor's chosen interface language (stored in a cookie). */
class SetLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $locale = $request->cookie(Locales::COOKIE);

        if (Locales::isSupported($locale)) {
            app()->setLocale($locale);
        }

        return $next($request);
    }
}
