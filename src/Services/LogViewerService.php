<?php

namespace Allanzico\LaravelHelios\Services;

use Allanzico\LaravelHelios\Support\Redactor;
use Illuminate\Support\Facades\File;

class LogViewerService
{
    public function getAllLogs(): array
    {
        $logPath = config('helios.log_path');

        if (!File::exists($logPath)) {
            return [];
        }

        $files = File::files($logPath);
        $logFiles = [];

        foreach ($files as $file) {
            if ($file->getExtension() === 'log') {
                $logFiles[] = [
                    'name' => $file->getFilename(),
                    'path' => $file->getPathname(),
                    'size' => $this->formatBytes($file->getSize()),
                ];
            }
        }

        // Sort files by name, newest first
        rsort($logFiles);

        return $logFiles;
    }

    /**
     * Get the content of a specific log file.
     *
     * @param  string  $fileName
     * @return string|null
     */
    public function getLogContent(string $fileName): ?string
    {
        if (str_contains($fileName, '..') || str_contains($fileName, '/') || str_contains($fileName, '\\')) {
            return null;
        }

        $filePath = config('helios.log_path') . '/' . $fileName;

        if (!File::exists($filePath)) {
            return null;
        }

        // Call the new, efficient method to read the end of the file.
        return app(Redactor::class)->logContent($this->readLastLinesFromFile($filePath, 500));
    }

    /**
     * Efficiently read the last N lines from a file.
     *
     * @param  string  $path
     * @param  int  $lines
     * @param  int  $buffer
     * @return string
     */
    private function readLastLinesFromFile(string $path, int $lines, int $buffer = 4096): string
    {
        $f = fopen($path, 'rb');
        fseek($f, 0, SEEK_END);

        if (ftell($f) === 0) {
            return '';
        }

        $output = '';
        $chunk = '';
        $lineCount = 0;

        while (ftell($f) > 0 && $lineCount <= $lines) {
            $seek = min(ftell($f), $buffer);
            fseek($f, -$seek, SEEK_CUR);
            $output = ($chunk = fread($f, $seek)) . $output;
            fseek($f, -mb_strlen($chunk, '8bit'), SEEK_CUR);
            $lineCount += substr_count($chunk, "\n");
        }

        while ($lineCount > $lines) {
            $output = substr($output, strpos($output, "\n") + 1);
            $lineCount--;
        }

        fclose($f);

        return $output;
    }

    private function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        if ($bytes == 0) return '0 ' . $units[0];

        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);

        $bytes /= (1 << (10 * $pow));

        return round($bytes, $precision) . ' ' . $units[$pow];
    }
}
