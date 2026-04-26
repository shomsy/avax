<?php

declare(strict_types=1);

namespace Avax\Cache\Tests\Unit\Cache;

use Avax\Cache\Cache;
use Avax\Cache\CompiledCache;
use PHPUnit\Framework\TestCase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

final class NoTimeFunctionInSystemTest extends TestCase
{
    public function test_no_time_function_in_system() : void
    {
        $cacheDir  = dirname(__DIR__, 3);
        $systemDir = $cacheDir . '/System';

        $filesWithTime = [];

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($systemDir)
        );

        foreach ($iterator as $file) {
            if ($file->isDir() || $file->getExtension() !== 'php') {
                continue;
            }

            $content = file_get_contents($file->getPathname());

            if (preg_match('/\btime\(\)/', $content)) {
                $relativePath    = str_replace($systemDir . '/', '', $file->getPathname());
                $filesWithTime[] = $relativePath;
            }
        }

        $this->assertEmpty(
            $filesWithTime,
            'Found time() calls in System/: ' . implode(', ', $filesWithTime)
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
                "Class $class should be autoloadable"
            );
        }
    }

    public function test_cache_has_read_method() : void
    {
        $this->assertTrue(
            method_exists(Cache::class, 'read'),
            'Cache::read() should exist'
        );
    }
}