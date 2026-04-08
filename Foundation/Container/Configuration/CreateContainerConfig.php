<?php

declare(strict_types=1);

namespace Avax\Container\Configuration;

/**
 * Immutable build-time options for assembling a container runtime.
 */
final readonly class CreateContainerConfig
{
    public const EXECUTION_MODE_DYNAMIC = 'dynamic';

    public const EXECUTION_MODE_COMPILED = 'compiled';

    public const EXECUTION_MODE_GENERATED = 'generated';

    public const PRUNE_MODE_NONE = 'none';

    public const PRUNE_MODE_STRICT = 'strict';

    public const POLICY_PROFILE_RELAXED = 'relaxed';

    public const POLICY_PROFILE_BALANCED = 'balanced';

    public const POLICY_PROFILE_STRICT = 'strict';

    public const ASYNC_TARGET_FPM = 'fpm';

    public const ASYNC_TARGET_WORKER = 'worker';

    public const ASYNC_TARGET_COROUTINE = 'coroutine';

    public const ASYNC_TARGET_FIBER = 'fiber';

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
        public string $diagnosticsMode = self::DIAGNOSTICS_MODE_MINIMAL,
        public string $executionMode = self::EXECUTION_MODE_COMPILED,
        public string $pruneMode = self::PRUNE_MODE_NONE,
        public string $policyProfile = self::POLICY_PROFILE_BALANCED,
        public string $asyncTarget = self::ASYNC_TARGET_FPM
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
        string $diagnosticsMode = self::DIAGNOSTICS_MODE_MINIMAL,
        string $executionMode = self::EXECUTION_MODE_COMPILED,
        string $pruneMode = self::PRUNE_MODE_NONE,
        string $policyProfile = self::POLICY_PROFILE_BALANCED,
        string $asyncTarget = self::ASYNC_TARGET_FPM
    ) : self
    {
        return new self(
            cacheDir    : $cacheDir,
            cacheVersion: $cacheVersion,
            debug       : $debug,
            settings    : $settings,
            strict      : $strict,
            compileMode : self::normalizeCompileMode(mode: $compileMode),
            diagnosticsMode: self::normalizeDiagnosticsMode(mode: $diagnosticsMode),
            executionMode: self::normalizeExecutionMode(mode: $executionMode),
            pruneMode   : self::normalizePruneMode(mode: $pruneMode),
            policyProfile: self::normalizePolicyProfile(profile: $policyProfile),
            asyncTarget : self::normalizeAsyncTarget(target: $asyncTarget)
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
            diagnosticsMode: $this->diagnosticsMode,
            executionMode: $this->executionMode,
            pruneMode   : $this->pruneMode,
            policyProfile: $this->policyProfile,
            asyncTarget : $this->asyncTarget
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
            diagnosticsMode: $this->diagnosticsMode,
            executionMode: $this->executionMode,
            pruneMode   : $this->pruneMode,
            policyProfile: $this->policyProfile,
            asyncTarget : $this->asyncTarget
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
            diagnosticsMode: $this->diagnosticsMode,
            executionMode: $this->executionMode,
            pruneMode   : $this->pruneMode,
            policyProfile: $this->policyProfile,
            asyncTarget : $this->asyncTarget
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
            diagnosticsMode: $this->diagnosticsMode,
            executionMode: $this->executionMode,
            pruneMode   : $this->pruneMode,
            policyProfile: $this->policyProfile,
            asyncTarget : $this->asyncTarget
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
            diagnosticsMode: $this->diagnosticsMode,
            executionMode: $this->executionMode,
            pruneMode   : $this->pruneMode,
            policyProfile: $this->policyProfile,
            asyncTarget : $this->asyncTarget
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
            diagnosticsMode: $this->diagnosticsMode,
            executionMode: $this->executionMode,
            pruneMode   : $this->pruneMode,
            policyProfile: $this->policyProfile,
            asyncTarget : $this->asyncTarget
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
            diagnosticsMode: self::normalizeDiagnosticsMode(mode: $diagnosticsMode),
            executionMode: $this->executionMode,
            pruneMode   : $this->pruneMode,
            policyProfile: $this->policyProfile,
            asyncTarget : $this->asyncTarget
        );
    }

    public function withExecutionMode(string $executionMode) : self
    {
        return new self(
            cacheDir    : $this->cacheDir,
            cacheVersion: $this->cacheVersion,
            debug       : $this->debug,
            settings    : $this->settings,
            strict      : $this->strict,
            compileMode : $this->compileMode,
            diagnosticsMode: $this->diagnosticsMode,
            executionMode: self::normalizeExecutionMode(mode: $executionMode),
            pruneMode   : $this->pruneMode,
            policyProfile: $this->policyProfile,
            asyncTarget : $this->asyncTarget
        );
    }

    public function withPruneMode(string $pruneMode) : self
    {
        return new self(
            cacheDir    : $this->cacheDir,
            cacheVersion: $this->cacheVersion,
            debug       : $this->debug,
            settings    : $this->settings,
            strict      : $this->strict,
            compileMode : $this->compileMode,
            diagnosticsMode: $this->diagnosticsMode,
            executionMode: $this->executionMode,
            pruneMode   : self::normalizePruneMode(mode: $pruneMode),
            policyProfile: $this->policyProfile,
            asyncTarget : $this->asyncTarget
        );
    }

    public function withPolicyProfile(string $policyProfile) : self
    {
        return new self(
            cacheDir    : $this->cacheDir,
            cacheVersion: $this->cacheVersion,
            debug       : $this->debug,
            settings    : $this->settings,
            strict      : $this->strict,
            compileMode : $this->compileMode,
            diagnosticsMode: $this->diagnosticsMode,
            executionMode: $this->executionMode,
            pruneMode   : $this->pruneMode,
            policyProfile: self::normalizePolicyProfile(profile: $policyProfile),
            asyncTarget : $this->asyncTarget
        );
    }

    public function withAsyncTarget(string $asyncTarget) : self
    {
        return new self(
            cacheDir    : $this->cacheDir,
            cacheVersion: $this->cacheVersion,
            debug       : $this->debug,
            settings    : $this->settings,
            strict      : $this->strict,
            compileMode : $this->compileMode,
            diagnosticsMode: $this->diagnosticsMode,
            executionMode: $this->executionMode,
            pruneMode   : $this->pruneMode,
            policyProfile: $this->policyProfile,
            asyncTarget : self::normalizeAsyncTarget(target: $asyncTarget)
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
            'executionMode' => $this->executionMode,
            'pruneMode' => $this->pruneMode,
            'policyProfile' => $this->policyProfile,
            'asyncTarget' => $this->asyncTarget,
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
        return $this->executionMode !== self::EXECUTION_MODE_DYNAMIC;
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

    public function usesDynamicExecution() : bool
    {
        return $this->executionMode === self::EXECUTION_MODE_DYNAMIC;
    }

    public function usesGeneratedExecution() : bool
    {
        return $this->executionMode === self::EXECUTION_MODE_GENERATED;
    }

    public function usesStrictPruning() : bool
    {
        return $this->pruneMode === self::PRUNE_MODE_STRICT;
    }

    public function supportsAsyncTarget() : bool
    {
        return in_array($this->asyncTarget, [self::ASYNC_TARGET_FPM, self::ASYNC_TARGET_WORKER], true);
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

    private static function normalizeExecutionMode(string $mode) : string
    {
        return match ($mode) {
            self::EXECUTION_MODE_DYNAMIC,
            self::EXECUTION_MODE_COMPILED,
            self::EXECUTION_MODE_GENERATED => $mode,
            default => self::EXECUTION_MODE_COMPILED,
        };
    }

    private static function normalizePruneMode(string $mode) : string
    {
        return match ($mode) {
            self::PRUNE_MODE_NONE,
            self::PRUNE_MODE_STRICT => $mode,
            default => self::PRUNE_MODE_NONE,
        };
    }

    private static function normalizePolicyProfile(string $profile) : string
    {
        return match ($profile) {
            self::POLICY_PROFILE_RELAXED,
            self::POLICY_PROFILE_BALANCED,
            self::POLICY_PROFILE_STRICT => $profile,
            default => self::POLICY_PROFILE_BALANCED,
        };
    }

    private static function normalizeAsyncTarget(string $target) : string
    {
        return match ($target) {
            self::ASYNC_TARGET_FPM,
            self::ASYNC_TARGET_WORKER,
            self::ASYNC_TARGET_COROUTINE,
            self::ASYNC_TARGET_FIBER => $target,
            default => self::ASYNC_TARGET_FPM,
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
