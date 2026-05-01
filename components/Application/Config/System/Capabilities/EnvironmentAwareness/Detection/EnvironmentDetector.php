<?php

declare(strict_types=1);

namespace Avax\Components\Application\Config\System\Capabilities\EnvironmentAwareness\System\Capabilities\Detection;

final class EnvironmentDetector
{
    private const string ENV_LOCAL = 'local';

    private const string ENV_STAGING = 'staging';

    private const string ENV_PRODUCTION = 'production';

    private const string ENV_TESTING = 'testing';

    public function detect() : string
    {
        $env = getenv('APP_ENV') ?: ($_ENV['APP_ENV'] ?? 'local');

        if ($env === '') {
            return self::ENV_LOCAL;
        }

        if (in_array($env, [self::ENV_LOCAL, self::ENV_STAGING, self::ENV_PRODUCTION, self::ENV_TESTING], true)) {
            return $env;
        }

        return self::ENV_LOCAL;
    }

    public function detectRuntime() : string
    {
        if (defined('FRANKENPHP')) {
            return 'frankenphp';
        }

        if (isset($_SERVER['RR_WORKER'])) {
            return 'roadrunner';
        }

        if (isset($_SERVER['SWOOLE'])) {
            return 'swoole';
        }

        if (PHP_SAPI === 'cli') {
            return 'cli';
        }

        return 'fpm';
    }

    public function detectContainer() : string|null
    {
        if (getenv('KUBERNETES_SERVICE_HOST')) {
            return 'kubernetes';
        }

        if (getenv('DOCKER_CONTAINER')) {
            return 'docker';
        }

        if (is_file('/.dockerenv')) {
            return 'docker';
        }

        return null;
    }

    public function isDebugEnabled() : bool
    {
        $debug = getenv('APP_DEBUG');

        return $debug === 'true' || $debug === '1';
    }

    public function isCI() : bool
    {
        return getenv('CI') === 'true'
            || getenv('GITHUB_ACTIONS') === 'true'
            || getenv('GITLAB_CI') === 'true';
    }
}