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

        $gate = config('helios.gates.view', config('helios.gate', 'viewHelios'));

        if (Gate::has($gate) && Gate::allows($gate)) {
            return $next($request);
        }

        abort(403, "Helios is not accessible in this environment without an explicit {$gate} gate.");
    }

    protected function isAllowedEnvironment(): bool
    {
        return app()->environment(config('helios.allowed_environments', ['local', 'testing']));
    }
}
