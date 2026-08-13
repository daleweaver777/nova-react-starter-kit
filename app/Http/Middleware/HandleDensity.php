<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Symfony\Component\HttpFoundation\Response;

class HandleDensity
{
    /**
     * Share the UI density with the root view so `<html data-density>` is stamped
     * server-side. The density preset in `app.css` keys off that attribute, so
     * rendering it here is what prevents a flash of the wrong scale on first paint.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        View::share('density', $request->cookie('density') ?? 'default');

        return $next($request);
    }
}
