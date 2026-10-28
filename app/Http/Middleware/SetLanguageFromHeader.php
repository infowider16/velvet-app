<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Session;

class SetLanguageFromHeader
{
   public function handle($request, $next)
    {
        $rawLocale = $request->get('lang')
            ?? $request->header('Accept-Language')
            ?? $request->header('lang_key')
            ?? session('locale')
            ?? $request->getPreferredLanguage(['en', 'de', 'ge']);

        $locale = strtolower(trim((string) $rawLocale));

        if (str_contains($locale, ',')) {
            $locale = explode(',', $locale)[0];
        }
        if (str_contains($locale, '-')) {
            $locale = explode('-', $locale)[0];
        }
        if (str_contains($locale, '_')) {
            $locale = explode('_', $locale)[0];
        }

        if (in_array($locale, ['de', 'ge'])) {
            $locale = 'ge';
        } else {
            $locale = 'en';
        }

        app()->setLocale($locale);

        return $next($request);
    }
}