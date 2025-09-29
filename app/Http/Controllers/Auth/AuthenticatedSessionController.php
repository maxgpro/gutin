<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class AuthenticatedSessionController extends Controller
{
    /**
     * Show the login page.
     */
    public function create(Request $request): Response
    {
        // Capture intended URL using official Redirector API to avoid manual session writes
        if (! $request->session()->has('url.intended')) {
            // 1) Explicit intended query param (only relative paths for safety)
            $explicit = $request->query('intended');
            if (is_string($explicit) && Str::startsWith($explicit, '/')) {
                redirect()->setIntendedUrl($explicit);
            } else {
                // 2) Fallback to Referer header (same-origin only, exclude auth-related pages)
                $referer = $request->headers->get('referer');
                $refererHost = is_string($referer) ? parse_url($referer, PHP_URL_HOST) : null;
                if ($referer && $refererHost === $request->getHost()) {
                    $path = parse_url($referer, PHP_URL_PATH) ?? '/';
                    $query = parse_url($referer, PHP_URL_QUERY);
                    if ($query) {
                        $path .= '?' . $query;
                    }

                    // Exclude auth/verification/reset routes to avoid loops
                    $excluded = [
                        '/login', '/register', '/forgot-password', '/reset-password',
                        '/confirm-password', '/verify-email', '/email', '/logout',
                    ];

                    if (Str::startsWith($path, '/')) {
                        if (! Str::startsWith($path, $excluded)) {
                            redirect()->setIntendedUrl($path);
                        }
                    }
                }
            }
        }

        return Inertia::render('auth/Login', [
            'canResetPassword' => Route::has('password.request'),
            'status' => $request->session()->get('status'),
        ]);
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        return redirect()->intended(route('dashboard', absolute: false));
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('blog.posts.index');
    }
}
