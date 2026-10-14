<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\URL;

class SetLocale
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        $locale = $request->route('locale') ?? $request->query('locale') ?? session('locale', config('app.locale'));

        if (in_array($locale, ['fr', 'en'], true)) {
            App::setLocale($locale);
            session()->put('locale', $locale);
        }

        if ($request->route('locale') && $request->route('locale') !== App::getLocale()) {
            URL::defaults(['locale' => App::getLocale()]);
        }

        return $next($request);
    }
}
