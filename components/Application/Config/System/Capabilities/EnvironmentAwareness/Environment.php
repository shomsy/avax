<?php

declare(strict_types=1);

namespace Avax\Components\Application\Config\System\Capabilities\EnvironmentAwareness;

use Avax\Components\Application\Config\System\Capabilities\EnvironmentAwareness\Detection\EnvironmentDetector;

final class Environment
{
    private static EnvironmentDetector $environmentDetector;

    public static function isLocal(): bool
    {
        return self::current() === 'local';
    }

    public static function current(): string
    {
        return self::detector()->detect();
    }

    private static function detector(): EnvironmentDetector
    {
        if (! isset(self::$environmentDetector)) {
            self::$environmentDetector = new EnvironmentDetector();
        }

        return self::$environmentDetector;
    }

    public static function isStaging(): bool
    {
        return self::current() === 'staging';
    }

    public static function isProduction(): bool
    {
        return self::current() === 'production';
    }

    public static function isTesting(): bool
    {
        return self::current() === 'testing';
    }

    public static function runtime(): string
    {
        return self::detector()->detectRuntime();
    }

    public static function container() : string|null
    {
        return self::detector()->detectContainer();
    }

    public static function config(): EnvironmentConfig
    {
        $env = self::current();

        return match ($env) {
            'local' => new LocalPolicy(),
            'staging' => new StagingPolicy(),
            'production' => new ProductionPolicy(),
            'testing' => new TestingPolicy(),
            default => new LocalPolicy(),
        };
    }
}
