<?php

namespace Allanzico\LaravelHelios\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\Response;

class Authorize
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($this->isAllowedEnvironment()) {
            return $next($request);
        }

        if (Gate::has(config('helios.gate', 'viewHelios')) && Gate::allows(config('helios.gate', 'viewHelios'))) {
            return $next($request);
        }

        abort(403);
    }

    protected function isAllowedEnvironment(): bool
    {
        return app()->environment(config('helios.allowed_environments', ['local', 'testing']));
    }
}
