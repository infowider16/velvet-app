<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Session;

class SetLanguageFromHeader
{
   public function handle($request, $next)
    {
        $locale = $request->get('lang') ?? session('locale') ?? $request->getPreferredLanguage(['en', 'ge']);
        
        if (in_array($locale, ['en', 'ge'])) {
            app()->setLocale($locale);
        }

        return $next($request);
    }
}