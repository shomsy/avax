<?php

declare(strict_types=1);

namespace Avax\Components\EnvironmentAwareness\System\PublicSurface;

use Avax\Components\EnvironmentAwareness\System\Capabilities\Detection\EnvironmentDetector;
use Avax\Components\EnvironmentAwareness\System\Capabilities\Policies\LocalPolicy;
use Avax\Components\EnvironmentAwareness\System\Capabilities\Policies\ProductionPolicy;
use Avax\Components\EnvironmentAwareness\System\Capabilities\Policies\StagingPolicy;
use Avax\Components\EnvironmentAwareness\System\Capabilities\Policies\TestingPolicy;

interface EnvironmentConfig
{
    public function errorDetail() : string;

    public function cacheEnabled() : bool;

    public function queueSync() : bool;

    public function securityHeadersStrict() : bool;

    public function debugEndpointsEnabled() : bool;

    public function queryLogEnabled() : bool;
}

final class Environment
{
    private static EnvironmentDetector $detector;

    public static function isLocal() : bool
    {
        return self::current() === 'local';
    }

    public static function current() : string
    {
        return self::detector()->detect();
    }

    private static function detector() : EnvironmentDetector
    {
        if (! isset(self::$detector)) {
            self::$detector = new EnvironmentDetector();
        }

        return self::$detector;
    }

    public static function isStaging() : bool
    {
        return self::current() === 'staging';
    }

    public static function isProduction() : bool
    {
        return self::current() === 'production';
    }

    public static function isTesting() : bool
    {
        return self::current() === 'testing';
    }

    public static function runtime() : string
    {
        return self::detector()->detectRuntime();
    }

    public static function container() : string|null
    {
        return self::detector()->detectContainer();
    }

    public static function config() : EnvironmentConfig
    {
        $env = self::current();

        return match ($env) {
            'local'      => new LocalPolicy(),
            'staging'    => new StagingPolicy(),
            'production' => new ProductionPolicy(),
            'testing'    => new TestingPolicy(),
            default      => new LocalPolicy(),
        };
    }
}