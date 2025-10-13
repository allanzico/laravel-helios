<?php

namespace Allanzico\LaravelHelios\Support;

use Illuminate\Support\HtmlString;

class Vite
{
    protected static ?array $manifest = null;
    protected static ?string $css = null;
    protected static ?string $js = null;

    /**
     * Get the Vite manifest for the package.
     */
    protected static function manifest(): array
    {
        if (static::$manifest !== null) {
            return static::$manifest;
        }

        $manifestPath = __DIR__.'/../../public/.vite/manifest.json';

        if (!file_exists($manifestPath)) {
            throw new \RuntimeException('Vite manifest not found. Please build the assets first: cd ui && npm run build');
        }

        static::$manifest = json_decode(file_get_contents($manifestPath), true);

        return static::$manifest;
    }

    /**
     * Get the CSS content.
     */
    protected static function css(): string
    {
        if (static::$css !== null) {
            return static::$css;
        }

        $manifest = static::manifest();
        $entry = $manifest['index.html'] ?? null;

        if (!$entry || !isset($entry['css'])) {
            return '';
        }

        $cssContent = '';
        foreach ($entry['css'] as $cssFile) {
            $cssPath = __DIR__.'/../../public/'.$cssFile;

            if (!file_exists($cssPath)) {
                throw new \RuntimeException("CSS file not found: {$cssFile}");
            }

            $cssContent .= file_get_contents($cssPath);
        }

        static::$css = $cssContent;

        return static::$css;
    }

    /**
     * Get the JavaScript content.
     */
    protected static function js(): string
    {
        if (static::$js !== null) {
            return static::$js;
        }

        $manifest = static::manifest();
        $entry = $manifest['index.html'] ?? null;

        if (!$entry) {
            throw new \RuntimeException('Entry point not found in Vite manifest.');
        }

        $jsPath = __DIR__.'/../../public/'.$entry['file'];

        if (!file_exists($jsPath)) {
            throw new \RuntimeException("JavaScript file not found: {$entry['file']}");
        }

        static::$js = file_get_contents($jsPath);

        return static::$js;
    }

    /**
     * Generate inline script and style tags for the Helios frontend assets.
     * Assets are served inline (like Laravel Horizon) to avoid needing to publish files.
     */
    public static function assets(): HtmlString
    {
        $css = static::css();
        $js = static::js();

        return new HtmlString(<<<HTML
<style data-helios-styles>{$css}</style>
<script type="module" data-helios-script>{$js}</script>
HTML);
    }
}
