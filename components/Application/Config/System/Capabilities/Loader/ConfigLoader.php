<?php

declare(strict_types=1);

namespace Avax\Components\Application\Config\System\Capabilities\Loader;

use RuntimeException;

final class ConfigLoader
{
    public function load(string $path) : array
    {
        if (is_dir($path)) {
            return $this->loadDirectory($path);
        }

        return $this->loadFile($path);
    }

    private function loadDirectory(string $directory) : array
    {
        $config = [];
        $files = glob(rtrim($directory, '/') . '/*.php');

        foreach ($files as $file) {
            $namespace = pathinfo($file, PATHINFO_FILENAME);
            $config[$namespace] = $this->loadFile($file);
        }

        return $config;
    }

    private function loadFile(string $file) : array
    {
        if (! file_exists($file)) {
            throw new RuntimeException('Config file not found: ' . $file);
        }

        $data = require $file;

        if (! is_array($data)) {
            throw new RuntimeException('Config file must return an array: ' . $file);
        }

        return $data;
    }
}
