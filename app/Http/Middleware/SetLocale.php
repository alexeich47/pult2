<?php

namespace App\Http\Middleware;

use App\Support\PultEnums;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $locale = $request->session()->get('locale')
            ?? $request->cookie('locale')
            ?? $request->getPreferredLanguage(PultEnums::supportedLocales())
            ?? config('app.locale');

        if (in_array($locale, PultEnums::supportedLocales(), true)) {
            app()->setLocale($locale);
        }

        return $next($request);
    }
}
