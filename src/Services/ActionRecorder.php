<?php

namespace Allanzico\LaravelHelios\Services;

use Allanzico\LaravelHelios\Models\HeliosAction;
use Allanzico\LaravelHelios\Support\Redactor;
use Illuminate\Support\Facades\Schema;
use Throwable;

class ActionRecorder
{
    public function record(
        string $action,
        ?string $targetType = null,
        ?string $targetId = null,
        string $status = 'requested',
        array $metadata = []
    ): void {
        try {
            if (! Schema::hasTable('helios_actions')) {
                return;
            }

            $request = app()->bound('request') ? request() : null;

            HeliosAction::create([
                'action' => $action,
                'target_type' => $targetType,
                'target_id' => $targetId,
                'status' => $status,
                'actor_id' => config('helios.security.store_user_id', false) ? $request?->user()?->getAuthIdentifier() : null,
                'ip_address' => config('helios.security.store_ip_address', false) ? $request?->ip() : null,
                'user_agent' => config('helios.security.store_user_agent', false) ? $request?->userAgent() : null,
                'metadata' => app(Redactor::class)->redact($metadata),
                'created_at' => now(),
            ]);
        } catch (Throwable) {
        }
    }
}
