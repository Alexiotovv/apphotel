<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;

class Localization
{
    public function handle(Request $request, Closure $next)
    {
        // 1. Prioridad: idioma desde la URL (ej: /en/home)
        if ($request->has('locale')) {
            $locale = $request->get('locale');
            if (array_key_exists($locale, config('app.available_locales'))) {
                App::setLocale($locale);
                Session::put('locale', $locale);
            }
        }
        // 2. Si no, revisa la sesión
        elseif (Session::has('locale')) {
            App::setLocale(Session::get('locale'));
        }
        // 3. Finalmente, usa el idioma por defecto configurado en `config/app.php`
        else {
            App::setLocale(config('app.locale'));
        }

        return $next($request);
    }
}