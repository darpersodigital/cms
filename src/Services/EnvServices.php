<?php

namespace Darpersodigital\Cms\Services;

use Darpersodigital\Cms\Models\GoogleAnalytic;
use Illuminate\Support\Facades\File;

class EnvServices
{

    public function setEnvValue(string $key, string $value): void
    {
        $envPath = base_path('.env');
        if (!File::exists($envPath)) {
            return;
        }

        $escapedValue = str_replace(['\\', '"'], ['\\\\', '\\"'], $value);
        $line = $key . '="' . $escapedValue . '"';
        $content = File::get($envPath);
        $keyPattern = '/^' . preg_quote($key, '/') . '=.*$/m';

        if (preg_match($keyPattern, $content)) {
            $content = preg_replace($keyPattern, $line, $content, 1);
        } else {
            $content = rtrim($content) . PHP_EOL . $line . PHP_EOL;
        }

        File::put($envPath, $content);
    }

    public function removeEnvValue(string $key): void
    {
        $envPath = base_path('.env');
        if (!File::exists($envPath)) {
            return;
        }

        $content = File::get($envPath);
        $updatedContent = preg_replace(
            '/^' . preg_quote($key, '/') . '=.*(?:\R|$)/m',
            '',
            $content
        );

        if ($updatedContent !== null) {
            File::put($envPath, rtrim($updatedContent) . PHP_EOL);
        }
    }
}
