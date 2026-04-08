<?php

declare(strict_types=1);

namespace Avax\Container\Configuration;

/**
 * Immutable build-time options for assembling a container runtime.
 */
final readonly class CreateContainerConfig
{
    public const COMPILE_MODE_DEV = 'dev';

    public const COMPILE_MODE_CI = 'ci';

    public const COMPILE_MODE_PRODUCTION = 'production';

    public const COMPILE_MODE_WARMUP = 'warmup';

    public const DIAGNOSTICS_MODE_MINIMAL = 'minimal';

    public const DIAGNOSTICS_MODE_DETAILED = 'detailed';

    public const DIAGNOSTICS_MODE_CI = 'ci';

    /**
     * @param array<string, mixed> $settings
     */
    public function __construct(
        public string $cacheDir = '',
        public string $cacheVersion = 'container-v1',
        public bool $debug = false,
        public array $settings = [],
        public bool $strict = false,
        public string $compileMode = self::COMPILE_MODE_PRODUCTION,
        public string $diagnosticsMode = self::DIAGNOSTICS_MODE_MINIMAL
    ) {}

    /**
     * @param array<string, mixed> $settings
     */
    public static function create(
        string $cacheDir = '',
        string $cacheVersion = 'container-v1',
        bool $debug = false,
        array $settings = [],
        bool $strict = false,
        string $compileMode = self::COMPILE_MODE_PRODUCTION,
        string $diagnosticsMode = self::DIAGNOSTICS_MODE_MINIMAL
    ) : self
    {
        return new self(
            cacheDir    : $cacheDir,
            cacheVersion: $cacheVersion,
            debug       : $debug,
            settings    : $settings,
            strict      : $strict,
            compileMode : self::normalizeCompileMode(mode: $compileMode),
            diagnosticsMode: self::normalizeDiagnosticsMode(mode: $diagnosticsMode)
        );
    }

    /**
     * @param array<string, mixed> $settings
     */
    public function withSettings(array $settings) : self
    {
        return new self(
            cacheDir    : $this->cacheDir,
            cacheVersion: $this->cacheVersion,
            debug       : $this->debug,
            settings    : $settings,
            strict      : $this->strict,
            compileMode : $this->compileMode,
            diagnosticsMode: $this->diagnosticsMode
        );
    }

    public function withDebug(bool $debug) : self
    {
        return new self(
            cacheDir    : $this->cacheDir,
            cacheVersion: $this->cacheVersion,
            debug       : $debug,
            settings    : $this->settings,
            strict      : $this->strict,
            compileMode : $this->compileMode,
            diagnosticsMode: $this->diagnosticsMode
        );
    }

    public function withStrict(bool $strict) : self
    {
        return new self(
            cacheDir    : $this->cacheDir,
            cacheVersion: $this->cacheVersion,
            debug       : $this->debug,
            settings    : $this->settings,
            strict      : $strict,
            compileMode : $this->compileMode,
            diagnosticsMode: $this->diagnosticsMode
        );
    }

    public function withCacheDir(string $cacheDir) : self
    {
        return new self(
            cacheDir    : $cacheDir,
            cacheVersion: $this->cacheVersion,
            debug       : $this->debug,
            settings    : $this->settings,
            strict      : $this->strict,
            compileMode : $this->compileMode,
            diagnosticsMode: $this->diagnosticsMode
        );
    }

    public function withCacheVersion(string $cacheVersion) : self
    {
        return new self(
            cacheDir    : $this->cacheDir,
            cacheVersion: $cacheVersion,
            debug       : $this->debug,
            settings    : $this->settings,
            strict      : $this->strict,
            compileMode : $this->compileMode,
            diagnosticsMode: $this->diagnosticsMode
        );
    }

    public function withCompileMode(string $compileMode) : self
    {
        return new self(
            cacheDir    : $this->cacheDir,
            cacheVersion: $this->cacheVersion,
            debug       : $this->debug,
            settings    : $this->settings,
            strict      : $this->strict,
            compileMode : self::normalizeCompileMode(mode: $compileMode),
            diagnosticsMode: $this->diagnosticsMode
        );
    }

    public function withDiagnosticsMode(string $diagnosticsMode) : self
    {
        return new self(
            cacheDir    : $this->cacheDir,
            cacheVersion: $this->cacheVersion,
            debug       : $this->debug,
            settings    : $this->settings,
            strict      : $this->strict,
            compileMode : $this->compileMode,
            diagnosticsMode: self::normalizeDiagnosticsMode(mode: $diagnosticsMode)
        );
    }

    public function environment() : string
    {
        $configured = $this->settings['app_env']
            ?? $this->settings['APP_ENV']
            ?? null;

        if (is_string($configured) && $configured !== '') {
            return $configured;
        }

        $environment = getenv('APP_ENV');

        return is_string($environment) ? $environment : '';
    }

    public function configHash() : string
    {
        return sha1(serialize([
            'cacheVersion' => $this->cacheVersion,
            'debug' => $this->debug,
            'strict' => $this->strict,
            'compileMode' => $this->compileMode,
            'diagnosticsMode' => $this->diagnosticsMode,
            'environment' => $this->environment(),
            'settings' => $this->settings,
        ]));
    }

    public function settingsFingerprint() : string
    {
        return sha1(serialize($this->settings));
    }

    public function benchmarkBuildMarker() : string
    {
        $configured = $this->settings['benchmarkBuildMarker']
            ?? $this->settings['benchmark']['build_marker']
            ?? $this->settings['BENCHMARK_BUILD_MARKER']
            ?? null;

        if (is_string($configured) && $configured !== '') {
            return $configured;
        }

        $environment = getenv('BENCHMARK_BUILD_MARKER');

        return is_string($environment) ? $environment : '';
    }

    public function usesDetailedDiagnostics() : bool
    {
        if (in_array($this->diagnosticsMode, [self::DIAGNOSTICS_MODE_DETAILED, self::DIAGNOSTICS_MODE_CI], true)) {
            return true;
        }

        return $this->debug || in_array(
            $this->compileMode,
            [self::COMPILE_MODE_CI, self::COMPILE_MODE_WARMUP],
            true
        );
    }

    public function validatesCompiledArtifactsOnLoad() : bool
    {
        return true;
    }

    public function validatesBeforeCompile() : bool
    {
        return $this->strict || in_array(
            $this->compileMode,
            [self::COMPILE_MODE_CI, self::COMPILE_MODE_WARMUP],
            true
        );
    }

    public function failsClosedOnCompiledCorruption() : bool
    {
        return $this->strict || in_array(
            $this->compileMode,
            [self::COMPILE_MODE_CI, self::COMPILE_MODE_PRODUCTION, self::COMPILE_MODE_WARMUP],
            true
        );
    }

    private static function normalizeCompileMode(string $mode) : string
    {
        return match ($mode) {
            self::COMPILE_MODE_DEV,
            self::COMPILE_MODE_CI,
            self::COMPILE_MODE_PRODUCTION,
            self::COMPILE_MODE_WARMUP => $mode,
            default => self::COMPILE_MODE_PRODUCTION,
        };
    }

    private static function normalizeDiagnosticsMode(string $mode) : string
    {
        return match ($mode) {
            self::DIAGNOSTICS_MODE_MINIMAL,
            self::DIAGNOSTICS_MODE_DETAILED,
            self::DIAGNOSTICS_MODE_CI => $mode,
            default => self::DIAGNOSTICS_MODE_MINIMAL,
        };
    }
}
