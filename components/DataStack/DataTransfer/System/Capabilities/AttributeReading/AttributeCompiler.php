<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\DataTransfer\System\Capabilities\AttributeReading;

use Avax\Components\Application\Filesystem\System\PublicSurface\Filesystem;
use RuntimeException;

/**
 * AttributeCompiler — 2-tier attribute metadata resolution.
 *
 * Tier 1: Compiled disk metadata (if configured + valid + not stale)
 * Tier 2: Live reflection (ultimate fallback)
 *
 * Unlike DataShapeCompiler which handles DataShape-specific metadata,
 * this orchestrator handles general-purpose class attribute compilation
 * for ORM, Validation, Container, and other consumers.
 */
final class AttributeCompiler
{
    /** @var array<string, CompiledAttributeMetadata> */
    private static array $resolvedCache = [];

    private CompileClassAttributes|null $compiler;
    private string                  $configHash;

    public function __construct(string|null $cacheDir = null, string|null $configHash = null, CompileClassAttributes|null $compiler = null,
    )
    {
        if ($compiler !== null) {
            $this->compiler   = $compiler;
            $this->configHash = $configHash ?? 'default';
        } elseif ($cacheDir !== null) {
            $this->compiler   = new CompileClassAttributes(
                cacheDir  : $cacheDir,
                configHash: $configHash ?? 'default',
                filesystem: new Filesystem(),
            );
            $this->configHash = $configHash ?? 'default';
        } else {
            $this->compiler   = null;
            $this->configHash = $configHash ?? 'default';
        }
    }

    /**
     * Reset the in-memory cache for long-lived worker safety.
     */
    public static function reset() : void
    {
        self::$resolvedCache = [];
    }

    /**
     * Compile and cache attributes for a class.
     *
     * @template T of object
     * @param class-string<T> $class
     */
    public function compile(string $class) : CompiledAttributeMetadata
    {
        if ($this->compiler === null) {
            throw new RuntimeException('AttributeCompiler: no compiler configured. Provide a cacheDir or CompileClassAttributes instance.');
        }

        $metadata                       = $this->compiler->compile($class);
        $cacheKey                       = $class . ':' . $this->configHash;
        self::$resolvedCache[$cacheKey] = $metadata;

        return $metadata;
    }

    /**
     * Compile attributes for multiple classes.
     *
     * @param list<class-string> $classes
     *
     * @return array<string, CompiledAttributeMetadata>
     */
    public function compileMany(array $classes) : array
    {
        if ($this->compiler === null) {
            throw new RuntimeException('AttributeCompiler: no compiler configured.');
        }

        $result = $this->compiler->compileMany($classes);
        foreach ($result as $class => $metadata) {
            $cacheKey                       = $class . ':' . $this->configHash;
            self::$resolvedCache[$cacheKey] = $metadata;
        }

        return $result;
    }

    /**
     * Check if compiled metadata is available and valid for a class.
     *
     * @param class-string $class
     */
    public function hasCompiled(string $class) : bool
    {
        return $this->resolve($class) !== null;
    }

    /**
     * Resolve compiled attribute metadata for a class.
     * Falls back to live reflection if compiled metadata is unavailable.
     *
     * @template T of object
     * @param class-string<T> $class
     */
    public function resolve(string $class) : CompiledAttributeMetadata|null
    {
        $cacheKey = $class . ':' . $this->configHash;

        if (isset(self::$resolvedCache[$cacheKey])) {
            return self::$resolvedCache[$cacheKey];
        }

        // Tier 1: compiled disk metadata
        if ($this->compiler !== null) {
            $compiled = $this->compiler->loadMetadata($class);
            if ($compiled !== null) {
                return self::$resolvedCache[$cacheKey] = $compiled;
            }
        }

        // No compiled metadata available — return null, caller should fall back to reflection
        return null;
    }
}
