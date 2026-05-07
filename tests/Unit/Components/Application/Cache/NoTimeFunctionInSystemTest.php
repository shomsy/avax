<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\Application\Cache;

use Avax\Components\Application\Cache\Cache;
use Avax\Components\Application\Cache\CompiledCache;
use Avax\Components\Application\Cache\System\PublicSurface\Exception\NotConfigured as CacheNotConfigured;
use PHPUnit\Framework\TestCase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

final class NoTimeFunctionInSystemTest extends TestCase
{
    public function test_no_time_function_in_system() : void
    {
        $systemDir = realpath(__DIR__ . '/../../../../../components/Application/Cache/System');
        if ($systemDir === false) {
            $this->fail('System directory not found');
        }

        $filesWithTime = [];

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($systemDir),
        );

        foreach ($iterator as $file) {
            if ($file->isDir()) {
                continue;
            }

            if ($file->getExtension() !== 'php') {
                continue;
            }

            $content = (string) file_get_contents($file->getRealPath());
            if (preg_match('/(?<!_)\btime\(\)/', $content)) {
                $relativePath    = str_replace($systemDir . '/', '', $file->getPathname());
                $filesWithTime[] = $relativePath;
            }
        }

        $this->assertEmpty(
            $filesWithTime,
            'Found time() calls in System/: ' . implode(', ', $filesWithTime),
        );
    }

    public function test_all_cache_classes_are_autoloadable() : void
    {
        $required = [
            Cache::class,
            CompiledCache::class,
        ];

        foreach ($required as $class) {
            $this->assertTrue(
                class_exists($class),
                sprintf('Class %s should be autoloadable', $class),
            );
        }
    }
}
