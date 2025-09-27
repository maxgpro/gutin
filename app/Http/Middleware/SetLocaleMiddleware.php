<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;

class SetLocaleMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $availableLocales = array_keys(config('app.available_locales'));
        
        // Check if locale is provided in URL parameter
        if ($request->has('locale') && in_array($request->get('locale'), $availableLocales)) {
            $locale = $request->get('locale');
            Session::put('locale', $locale);
        }
        // Check session for saved locale
        elseif (Session::has('locale') && in_array(Session::get('locale'), $availableLocales)) {
            $locale = Session::get('locale');
        }
        // Check Accept-Language header
        else {
            $acceptLanguage = $request->getPreferredLanguage($availableLocales);
            $locale = $acceptLanguage ?: config('app.locale');
            Session::put('locale', $locale);
        }

        App::setLocale($locale);

        // Share current locale with all views
        view()->share('currentLocale', $locale);
        view()->share('availableLocales', config('app.available_locales'));

        return $next($request);
    }
}
