<?php

declare(strict_types=1);

namespace Avax\Tests\Architecture\Components\Identity;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * Detects hidden construction patterns in Identity Flows and Capabilities.
 *
 * Flags instantiation of infrastructure services (Store, Engine, Coordinator, Registry)
 * and InMemory* classes outside of Configuration/Assembly namespaces.
 *
 * Allows: value objects, exceptions, self/static factories, same-namespace siblings,
 * standard library classes.
 */
final class IdentityHiddenConstructionTest extends TestCase
{
    private string $identityPath;

    protected function setUp(): void
    {
        $this->identityPath = dirname(__DIR__, 4) . '/components/Identity';
    }

    /**
     * Patterns that are always allowed — these are not hidden construction violations.
     */
    private const ALLOWED_SUFFIXES = [
        // Value objects
        'Id',
        'Email',
        'Name',
        'Data',
        'Result',
        'Context',
        'Challenge',
        'Payload',
        'Token',
        'Event',
        // Exceptions
        'Exception',
        'Failed',
        'Denied',
        'Unavailable',
        'Exceeded',
        'Required',
        'Conflict',
        'Error',
        // Standard library
        'DateTime',
        'DateTimeImmutable',
        'InvalidArgumentException',
        'RuntimeException',
        'LogicException',
        'Countable',
        'ArrayIterator',
    ];

    /**
     * Infrastructure patterns that should NOT be instantiated outside Configuration.
     */
    private const FORBIDDEN_SUFFIXES = [
        'Store',
        'Engine',
        'Coordinator',
        'Registry',
        'Repository',
        'Gateway',
    ];

    private function extractNamespace(string $content): ?string
    {
        if (preg_match('/^namespace\s+([^;]+);/m', $content, $match)) {
            return $match[1];
        }

        return null;
    }

    private function isInConfigurationNamespace(?string $namespace): bool
    {
        if ($namespace === null) {
            return false;
        }

        return str_contains($namespace, '\\Configuration\\')
            || str_contains($namespace, '\\Assembly\\');
    }

    private function isAllowedInstantiation(string $className): bool
    {
        // self/static references
        if (in_array($className, ['self', 'static'], true)) {
            return true;
        }

        // Standard library (no backslash = built-in or global)
        if (!str_contains($className, '\\')) {
            foreach (self::ALLOWED_SUFFIXES as $suffix) {
                if (str_ends_with($className, $suffix)) {
                    return true;
                }
            }

            return true; // Unknown non-namespaced class is allowed
        }

        // Check against allowed suffixes
        $shortName = substr($className, strrpos($className, '\\') + 1);

        foreach (self::ALLOWED_SUFFIXES as $suffix) {
            if (str_ends_with($shortName, $suffix)) {
                return true;
            }
        }

        return false;
    }

    private function isForbiddenInstantiation(string $className): bool
    {
        if (!str_contains($className, '\\')) {
            return false;
        }

        $shortName = substr($className, strrpos($className, '\\') + 1);

        foreach (self::FORBIDDEN_SUFFIXES as $suffix) {
            if (str_ends_with($shortName, $suffix)) {
                return true;
            }
        }

        if (str_starts_with($shortName, 'InMemory')) {
            return true;
        }

        return false;
    }

    #[Test]
    public function no_hidden_infrastructure_construction_in_flows_or_capabilities(): void
    {
        $scanDirs = ['/Flows/', '/Capabilities/'];
        $violations = [];

        $iterator = new \RecursiveDirectoryIterator($this->identityPath);
        $recursive = new \RecursiveIteratorIterator($iterator, \RecursiveIteratorIterator::LEAVES_ONLY);

        foreach ($recursive as $file) {
            if (!$file->isFile() || $file->getExtension() !== 'php') {
                continue;
            }

            $pathname = $file->getPathname();

            // Only scan Flows and Capabilities
            $isInScope = false;
            foreach ($scanDirs as $scanDir) {
                if (str_contains($pathname, $scanDir)) {
                    $isInScope = true;
                    break;
                }
            }

            if (!$isInScope) {
                continue;
            }

            $content = file_get_contents($pathname);
            if ($content === false) {
                continue;
            }

            $namespace = $this->extractNamespace($content);

            if ($this->isInConfigurationNamespace($namespace)) {
                continue;
            }

            // Find all `new ` instantiations
            if (!preg_match_all('/new\s+([A-Za-z_\\\\][A-Za-z0-9_\\\\]*)\s*\(/', $content, $matches)) {
                continue;
            }

            foreach ($matches[1] as $className) {
                if ($this->isAllowedInstantiation($className)) {
                    continue;
                }

                if ($this->isForbiddenInstantiation($className)) {
                    $violations[] = sprintf(
                        '%s instantiates forbidden %s',
                        $pathname,
                        $className,
                    );
                }
            }
        }

        $this->assertEmpty(
            $violations,
            "Flows and Capabilities must not instantiate infrastructure services:\n" . implode("\n", $violations),
        );
    }
}
