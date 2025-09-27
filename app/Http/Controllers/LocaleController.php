<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class LocaleController extends Controller
{
    /**
     * Switch application locale
     */
    public function switch(Request $request, string $locale): RedirectResponse
    {
        $availableLocales = array_keys(config('app.available_locales'));
        
        if (!in_array($locale, $availableLocales)) {
            abort(400, 'Unsupported locale');
        }

        Session::put('locale', $locale);
        
        return redirect()->back();
    }
}
