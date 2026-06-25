<?php

namespace Allanzico\LaravelHelios\HealthChecks\Checks;

use Allanzico\LaravelHelios\HealthChecks\HealthCheck;
use Allanzico\LaravelHelios\HealthChecks\HealthCheckResult;
use Illuminate\Support\Str;
use Throwable;

class StorageWritabilityHealthCheck extends HealthCheck
{
    public function run(): HealthCheckResult
    {
        try {
            $paths = config('helios.health.storage.paths', [
                storage_path('framework/cache'),
                storage_path('logs'),
            ]);

            $failures = [];
            $checked = [];

            foreach ($paths as $path) {
                $path = (string) $path;
                $checked[] = $path;

                if (! is_dir($path)) {
                    $failures[] = ['path' => $path, 'reason' => 'missing'];
                    continue;
                }

                if (! is_writable($path)) {
                    $failures[] = ['path' => $path, 'reason' => 'not writable'];
                    continue;
                }

                $testFile = rtrim($path, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR.'.helios-health-'.Str::random(12);

                if (file_put_contents($testFile, 'helios') === false) {
                    $failures[] = ['path' => $path, 'reason' => 'write failed'];
                    continue;
                }

                if (file_get_contents($testFile) !== 'helios') {
                    $failures[] = ['path' => $path, 'reason' => 'read failed'];
                }

                @unlink($testFile);
            }

            $this->shortSummary = count($failures) === 0
                ? count($checked).' writable'
                : count($failures).' failed';

            $this->meta = [
                'paths_checked' => $checked,
                'failures' => $failures,
            ];

            if (count($failures) > 0) {
                return $this->failed('One or more storage paths are not writable');
            }

            return $this->ok('Storage paths are writable');
        } catch (Throwable $e) {
            return $this->crashed('Could not check storage writability', $e);
        }
    }
}
