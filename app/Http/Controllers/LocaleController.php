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
    public function switch(LocaleSwitchRequest $request): JsonResponse | RedirectResponse
    {
        $locale = $request->validated('locale');
        
        Session::put('locale', $locale);
        
        
        // todo: delete loging
        \Log::info("Locale switched to: {$locale}");
        
        // Return JSON for AJAX requests, redirect for regular forms
        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'locale' => $locale,
                'message' => "Language switched to {$locale}"
            ]);
        }
        
        return redirect()->back()->with('success', "Language switched to {$locale}");
    }
}
