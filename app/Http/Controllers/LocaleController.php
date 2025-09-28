<?php

namespace App\Http\Controllers;

use App\Http\Requests\LocaleSwitchRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Session;

class LocaleController extends Controller
{
    /**
     * Switch application locale
     * Handles both AJAX and regular form requests
     */
    public function switch(LocaleSwitchRequest $request): JsonResponse|RedirectResponse
    {
        $locale = $request->validated('locale');
        
        Session::put('locale', $locale);
        
        // For Inertia requests, respond with a redirect so the client re-visits the current route
        // Use 303 See Other to ensure the subsequent request is a GET (recommended for POST form submissions)
        if ($request->header('X-Inertia')) {
            return redirect()->back(status: 303);
        }
        
    return redirect()->back()->with('success', __('ui.switch_language').': '.$locale);
    }
}
