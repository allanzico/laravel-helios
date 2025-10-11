<?php

namespace Allanzico\LaravelHelios\HealthChecks\Checks;

use Allanzico\LaravelHelios\HealthChecks\HealthCheck;
use Allanzico\LaravelHelios\HealthChecks\HealthCheckResult;

class DiskSpaceHealthCheck extends HealthCheck
{
    protected int $warningThreshold = 80; // percent
    protected int $errorThreshold = 90; // percent

    public function warningThreshold(int $percentage): static
    {
        $this->warningThreshold = $percentage;
        return $this;
    }

    public function errorThreshold(int $percentage): static
    {
        $this->errorThreshold = $percentage;
        return $this;
    }

    public function run(): HealthCheckResult
    {
        try {
            $diskPath = base_path();
            $freeSpace = disk_free_space($diskPath);
            $totalSpace = disk_total_space($diskPath);
            
            $usedSpace = $totalSpace - $freeSpace;
            $usedPercentage = round(($usedSpace / $totalSpace) * 100, 2);

            $this->shortSummary = "{$usedPercentage}% used";
            $this->meta = [
                'used_percentage' => $usedPercentage,
                'free_space_gb' => round($freeSpace / 1024 / 1024 / 1024, 2),
                'total_space_gb' => round($totalSpace / 1024 / 1024 / 1024, 2),
                'used_space_gb' => round($usedSpace / 1024 / 1024 / 1024, 2),
            ];

            if ($usedPercentage >= $this->errorThreshold) {
                return $this->failed("Disk space critically low: {$usedPercentage}% used");
            }

            if ($usedPercentage >= $this->warningThreshold) {
                return $this->warning("Disk space running low: {$usedPercentage}% used");
            }

            return $this->ok("Disk space healthy: {$usedPercentage}% used");
        } catch (\Throwable $e) {
            return $this->crashed("Could not check disk space", $e);
        }
    }
}