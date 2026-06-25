<?php

namespace Allanzico\LaravelHelios\Support;

use Illuminate\Support\Facades\Gate;

class ActionAuthorizer
{
    public function authorize(string $ability, string $action, string $message): void
    {
        abort_unless($this->allowed($ability, $action), 403, $message);
    }

    public function allowed(string $ability, string $action): bool
    {
        if (! (bool) config("helios.actions.{$action}", false)) {
            return false;
        }

        $gate = config("helios.gates.{$ability}");

        if (! is_string($gate) || $gate === '' || ! Gate::has($gate)) {
            return true;
        }

        return Gate::allows($gate);
    }
}
