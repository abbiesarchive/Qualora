<?php

namespace App\Http\Controllers;

use App\Support\Locales;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class LocaleController extends Controller
{
    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'locale' => ['required', 'string', Rule::in(array_keys(Locales::SUPPORTED))],
        ]);

        // One year. Kept in a cookie so it also survives logging out.
        return back()->withCookie(cookie(Locales::COOKIE, $validated['locale'], 60 * 24 * 365));
    }
}
