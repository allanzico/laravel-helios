<?php

namespace Allanzico\LaravelHelios\Http\Controllers\Api;

use Cron\CronExpression;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Console\Scheduling\Schedule;
use Allanzico\LaravelHelios\Models\HeliosScheduledTask;
use Allanzico\LaravelHelios\Models\HeliosTaskDefinition;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Process\Process;
use Throwable;

class ScheduledTaskController extends Controller
{
    public function index(): JsonResponse
    {
        $this->syncDiscoveredTasks();

        $definedTasks = HeliosTaskDefinition::all()->map(function ($taskDefinition) {
            $signature = $this->extractCommandSignature($taskDefinition->command);

            // Get the latest COMPLETED run for THIS specific command
            $latestRun = HeliosScheduledTask::query()
                ->where('command', $taskDefinition->command)
                ->whereNotNull('finished_at')
                ->orderBy('finished_at', 'desc')
                ->first();

            $result = [
                // Task Definition fields
                'command' => $taskDefinition->command,
                'signature' => $signature,
                'expression' => $taskDefinition->expression,
                'description' => $taskDefinition->description,
                'next_run_at' => (new CronExpression($taskDefinition->expression))->getNextRunDate()->format('c'),
            ];

            // Add latest run details if it exists
            if ($latestRun) {
                $result['latest_run'] = [
                    'id' => $latestRun->id,
                    'status' => $latestRun->status,
                    'started_at' => $latestRun->started_at?->format('c'),
                    'finished_at' => $latestRun->finished_at?->format('c'),
                    'runtime_ms' => $latestRun->runtime_ms,
                    'output' => $latestRun->output,
                    'exit_code' => $this->extractExitCode($latestRun->output, $latestRun->status),
                    'triggered_by' => $latestRun->triggered_by ?? 'scheduler',
                ];
            } else {
                $result['latest_run'] = null;
            }

            return $result;
        });

        return response()->json(['tasks' => $definedTasks]);
    }

    protected function syncDiscoveredTasks(): void
    {
        try {
            $events = collect(app(Schedule::class)->events())
                ->filter(fn ($event) => ! empty($event->command));

            if ($events->isEmpty()) {
                return;
            }

            $definedTasks = $events->mapWithKeys(function ($event) {
                return [$event->command => [
                    'command' => $event->command,
                    'expression' => $event->expression,
                    'description' => $event->description,
                ]];
            });

            HeliosTaskDefinition::query()
                ->whereNotIn('command', $definedTasks->keys())
                ->delete();

            foreach ($definedTasks as $task) {
                HeliosTaskDefinition::updateOrCreate(
                    ['command' => $task['command']],
                    $task
                );
            }
        } catch (Throwable) {
            // Some apps only expose the schedule from the console kernel. The
            // helios:sync-tasks command remains available for those cases.
        }
    }

    public function run(Request $request): StreamedResponse
    {
        abort_unless(config('helios.watchers.schedule.allow_manual_runs', true), 403, 'Manual scheduled task runs are disabled.');

        $validated = $request->validate(['signature' => 'required|string']);
        $signature = $validated['signature'];

        // Find the task definition by matching the signature
        $taskDefinition = HeliosTaskDefinition::all()->first(function ($task) use ($signature) {
            return $this->extractCommandSignature($task->command) === $signature;
        });

        if (!$taskDefinition) {
            abort(403, 'This command is not a scheduled task.');
        }

        return new StreamedResponse(function () use ($signature, $taskDefinition) {
            // Create a log entry for this manual run
            $taskLog = HeliosScheduledTask::create([
                'command' => $taskDefinition->command,
                'expression' => $taskDefinition->expression,
                'status' => 'starting',
                'started_at' => now(),
                'triggered_by' => 'manual',
            ]);

            header('Content-Type: text/event-stream');
            header('Cache-Control: no-cache');
            header('Connection: keep-alive');
            header('X-Accel-Buffering: no');

            $startTime = microtime(true);
            $command = [PHP_BINARY, 'artisan', $signature];
            
            $process = new Process($command, base_path());
            $process->setTimeout(3600);

            $allOutput = '';

            $process->run(function ($type, $buffer) use (&$allOutput) {
                $allOutput .= $buffer;
                if (Process::ERR === $type) {
                    $this->sendSseMessage('ERROR: ' . $buffer);
                } else {
                    $this->sendSseMessage($buffer);
                }
            });

            $runtime = (microtime(true) - $startTime) * 1000;

            // Update the task log with final status
            if (!$process->isSuccessful()) {
                $exitCode = $process->getExitCode();
                $finalOutput = $allOutput ?: '(No output produced)';
                $finalOutput .= "\n\n--- PROCESS FAILED ---";
                $finalOutput .= "\nExit Code: {$exitCode}";
                $finalOutput .= "\nDuration: " . round($runtime, 2) . "ms";
                
                $taskLog->update([
                    'status' => 'failed',
                    'finished_at' => now(),
                    'runtime_ms' => $runtime,
                    'output' => $finalOutput,
                ]);
                $this->sendSseMessage("\n--- PROCESS FAILED (Exit Code: {$exitCode}) ---");
            } else {
                $finalOutput = $allOutput ?: '(No output produced)';
                $finalOutput .= "\n\n--- PROCESS FINISHED ---";
                $finalOutput .= "\nStatus: Success";
                $finalOutput .= "\nDuration: " . round($runtime, 2) . "ms";
                
                $taskLog->update([
                    'status' => 'finished',
                    'finished_at' => now(),
                    'runtime_ms' => $runtime,
                    'output' => $finalOutput,
                ]);
                $this->sendSseMessage("\n--- PROCESS FINISHED ---");
            }
        });
    }

    private function sendSseMessage(string $data): void
    {
        echo "data: " . json_encode($data) . "\n\n";
        if (ob_get_level() > 0) {
            ob_flush();
        }
        flush();
    }

    /**
     * Extract the command signature from the full command string
     * Example: "'/usr/bin/php' 'artisan' app:test-command" -> "app:test-command"
     */
    private function extractCommandSignature(string $command): string
    {
        // Match pattern: 'artisan' followed by the command
        if (preg_match("/'artisan'\s+(.+?)(?:\s|$)/", $command, $matches)) {
            return trim($matches[1], " '\"");
        }
        
        // Fallback: if it's already just a command name
        return $command;
    }

    /**
     * Extract exit code from output if it contains a failure message
     */
    private function extractExitCode(?string $output, ?string $status): ?int
    {
        if (!$output && !$status) return null;
        
        // If status is finished, return 0
        if ($status === 'finished') {
            return 0;
        }
        
        // Check output for process finished message
        if ($output && strpos($output, '--- PROCESS FINISHED ---') !== false) {
            return 0;
        }
        
        // Check output for explicit exit code
        if ($output && preg_match('/Exit Code: (\d+)/', $output, $matches)) {
            return (int) $matches[1];
        }
        
        // If status is failed but no exit code found, return 1
        if ($status === 'failed') {
            return 1;
        }
        
        return null;
    }
}
