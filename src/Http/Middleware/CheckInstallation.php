<?php

namespace JacobTilly\LaraFort\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Cache;

class CheckInstallation
{
    public function handle($request, Closure $next)
    {
        if (!Cache::get('larafort_installing')) {
            return response('Unauthorized.', 403);
        }

        return $next($request);
    }
}
