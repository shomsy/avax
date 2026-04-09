<?php

declare(strict_types=1);

namespace Avax\Container\DI\Capabilities\Composition;

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

    public const POLICY_FAIL_MODE_OPEN = 'open';

    public const POLICY_FAIL_MODE_CLOSED = 'closed';

    public const SLICE_BOUNDARY_MODE_PROJECTED = 'projected';

    public const SLICE_BOUNDARY_MODE_STRICT = 'strict';

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
        public string $policyFailMode = self::POLICY_FAIL_MODE_CLOSED,
        /** @var array<string, string> */
        public array $policyProfiles = [],
        public string $sliceBoundaryMode = self::SLICE_BOUNDARY_MODE_STRICT,
        public string $asyncTarget = self::ASYNC_TARGET_FPM
    ) {}

    /**
     * @param array<string, mixed> $settings
     */
    public static function create(
        string|null $cacheDir = null,
        string|null $cacheVersion = null,
        bool|null   $debug = null,
        array|null  $settings = null,
        bool|null   $strict = null,
        string|null $compileMode = null,
        string|null $diagnosticsMode = null,
        string|null $executionMode = null,
        string|null $pruneMode = null,
        string|null $policyProfile = null,
        string|null $policyFailMode = null,
        array|null  $policyProfiles = null,
        string|null $sliceBoundaryMode = null,
        string      $asyncTarget = self::ASYNC_TARGET_FPM
    ) : self
    {
        $cacheDir          ??= '';
        $cacheVersion      ??= 'container-v1';
        $debug             ??= false;
        $settings          ??= [];
        $strict            ??= false;
        $compileMode       ??= self::COMPILE_MODE_PRODUCTION;
        $diagnosticsMode   ??= self::DIAGNOSTICS_MODE_MINIMAL;
        $executionMode     ??= self::EXECUTION_MODE_COMPILED;
        $pruneMode         ??= self::PRUNE_MODE_NONE;
        $policyProfile     ??= self::POLICY_PROFILE_BALANCED;
        $policyFailMode    ??= self::POLICY_FAIL_MODE_CLOSED;
        $policyProfiles    ??= [];
        $sliceBoundaryMode ??= self::SLICE_BOUNDARY_MODE_STRICT;

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
            policyFailMode: self::normalizePolicyFailMode(mode: $policyFailMode),
            policyProfiles: self::normalizePolicyProfiles(profiles: $policyProfiles),
            sliceBoundaryMode: self::normalizeSliceBoundaryMode(mode: $sliceBoundaryMode),
            asyncTarget : self::normalizeAsyncTarget(target: $asyncTarget)
        );
    }

    /**
     * @param array<string, mixed> $overrides
     */
    private function copy(array $overrides = []) : self
    {
        return new self(
            cacheDir    : (string) ($overrides['cacheDir'] ?? $this->cacheDir),
            cacheVersion: (string) ($overrides['cacheVersion'] ?? $this->cacheVersion),
            debug       : (bool) ($overrides['debug'] ?? $this->debug),
            settings    : $overrides['settings'] ?? $this->settings,
            strict      : (bool) ($overrides['strict'] ?? $this->strict),
            compileMode : isset($overrides['compileMode'])
                ? self::normalizeCompileMode(mode: (string) $overrides['compileMode'])
                : $this->compileMode,
            diagnosticsMode: isset($overrides['diagnosticsMode'])
                ? self::normalizeDiagnosticsMode(mode: (string) $overrides['diagnosticsMode'])
                : $this->diagnosticsMode,
            executionMode: isset($overrides['executionMode'])
                ? self::normalizeExecutionMode(mode: (string) $overrides['executionMode'])
                : $this->executionMode,
            pruneMode   : isset($overrides['pruneMode'])
                ? self::normalizePruneMode(mode: (string) $overrides['pruneMode'])
                : $this->pruneMode,
            policyProfile: isset($overrides['policyProfile'])
                ? self::normalizePolicyProfile(profile: (string) $overrides['policyProfile'])
                : $this->policyProfile,
            policyFailMode: isset($overrides['policyFailMode'])
                ? self::normalizePolicyFailMode(mode: (string) $overrides['policyFailMode'])
                : $this->policyFailMode,
            policyProfiles: array_key_exists('policyProfiles', $overrides)
                ? self::normalizePolicyProfiles(profiles: $overrides['policyProfiles'])
                : $this->policyProfiles,
            sliceBoundaryMode: isset($overrides['sliceBoundaryMode'])
                ? self::normalizeSliceBoundaryMode(mode: (string) $overrides['sliceBoundaryMode'])
                : $this->sliceBoundaryMode,
            asyncTarget : isset($overrides['asyncTarget'])
                ? self::normalizeAsyncTarget(target: (string) $overrides['asyncTarget'])
                : $this->asyncTarget
        );
    }

    /**
     * @param array<string, mixed> $settings
     */
    public function withSettings(array $settings) : self
    {
        return $this->copy(overrides: ['settings' => $settings]);
    }

    public function withDebug(bool $debug) : self
    {
        return $this->copy(overrides: ['debug' => $debug]);
    }

    public function withStrict(bool $strict) : self
    {
        return $this->copy(overrides: ['strict' => $strict]);
    }

    public function withCacheDir(string $cacheDir) : self
    {
        return $this->copy(overrides: ['cacheDir' => $cacheDir]);
    }

    public function withCacheVersion(string $cacheVersion) : self
    {
        return $this->copy(overrides: ['cacheVersion' => $cacheVersion]);
    }

    public function withCompileMode(string $compileMode) : self
    {
        return $this->copy(overrides: ['compileMode' => $compileMode]);
    }

    public function withDiagnosticsMode(string $diagnosticsMode) : self
    {
        return $this->copy(overrides: ['diagnosticsMode' => $diagnosticsMode]);
    }

    public function withExecutionMode(string $executionMode) : self
    {
        return $this->copy(overrides: ['executionMode' => $executionMode]);
    }

    public function withPruneMode(string $pruneMode) : self
    {
        return $this->copy(overrides: ['pruneMode' => $pruneMode]);
    }

    public function withPolicyProfile(string $policyProfile) : self
    {
        return $this->copy(overrides: ['policyProfile' => $policyProfile]);
    }

    public function withPolicyFailMode(string $policyFailMode) : self
    {
        return $this->copy(overrides: ['policyFailMode' => $policyFailMode]);
    }

    /**
     * @param array<string, string> $policyProfiles
     */
    public function withPolicyProfiles(array $policyProfiles) : self
    {
        return $this->copy(overrides: ['policyProfiles' => $policyProfiles]);
    }

    public function withSliceBoundaryMode(string $sliceBoundaryMode) : self
    {
        return $this->copy(overrides: ['sliceBoundaryMode' => $sliceBoundaryMode]);
    }

    public function withAsyncTarget(string $asyncTarget) : self
    {
        return $this->copy(overrides: ['asyncTarget' => $asyncTarget]);
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
            'policyFailMode' => $this->policyFailMode,
            'policyProfiles' => $this->policyProfiles,
            'sliceBoundaryMode' => $this->sliceBoundaryMode,
            'asyncTarget' => $this->asyncTarget,
            'environment' => $this->environment(),
            'settings' => $this->settings,
        ]));
    }

    public function effectivePolicyProfile() : string
    {
        $environment = $this->environment();

        return $this->policyProfiles[$environment]
            ?? $this->policyProfile;
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

    public function failsClosedOnPolicy() : bool
    {
        return $this->policyFailMode === self::POLICY_FAIL_MODE_CLOSED;
    }

    public function usesStrictSliceBoundaries() : bool
    {
        return $this->sliceBoundaryMode === self::SLICE_BOUNDARY_MODE_STRICT;
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

    private static function normalizePolicyFailMode(string $mode) : string
    {
        return match ($mode) {
            self::POLICY_FAIL_MODE_OPEN,
            self::POLICY_FAIL_MODE_CLOSED => $mode,
            default => self::POLICY_FAIL_MODE_CLOSED,
        };
    }

    /**
     * @param mixed $profiles
     * @return array<string, string>
     */
    private static function normalizePolicyProfiles(mixed $profiles) : array
    {
        if (! is_array($profiles)) {
            return [];
        }

        $normalized = [];

        foreach ($profiles as $environment => $profile) {
            if (! is_string($environment) || ! is_string($profile)) {
                continue;
            }

            $environment = trim($environment);
            if ($environment === '') {
                continue;
            }

            $normalized[$environment] = self::normalizePolicyProfile(profile: $profile);
        }

        ksort($normalized);

        return $normalized;
    }

    private static function normalizeSliceBoundaryMode(string $mode) : string
    {
        return match ($mode) {
            self::SLICE_BOUNDARY_MODE_PROJECTED,
            self::SLICE_BOUNDARY_MODE_STRICT => $mode,
            default => self::SLICE_BOUNDARY_MODE_STRICT,
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
