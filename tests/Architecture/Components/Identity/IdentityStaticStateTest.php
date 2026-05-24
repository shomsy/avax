<?php

declare(strict_types=1);

namespace Avax\Tests\Architecture\Components\Identity;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * Detects static mutable runtime state in Identity components.
 *
 * Uses file-based scanning (not Reflection) to avoid autoloading conflicts
 * from duplicate class declarations. Classifies static properties by reading
 * source code and checking for reset mechanisms.
 */
final class IdentityStaticStateTest extends TestCase
{
    private string $identityPath;

    protected function setUp(): void
    {
        $this->identityPath = dirname(__DIR__, 4) . '/components/Identity';
    }

    /**
     * Scan a PHP file for static mutable state declarations.
     *
     * @return array{namespace: string, class: string, properties: list<array{name: string, type: string, hasReset: bool}>}
     */
    private function scanFileForStaticState(string $filepath): array
    {
        $content = file_get_contents($filepath);
        if ($content === false) {
            return ['namespace' => '', 'class' => '', 'properties' => []];
        }

        $namespace = '';
        if (preg_match('/^namespace\s+([^;]+);/m', $content, $nsMatch)) {
            $namespace = $nsMatch[1];
        }

        $className = '';
        if (preg_match('/^\s*(?:final\s+)?(?:abstract\s+)?(?:readonly\s+)?class\s+(\w+)/m', $content, $classMatch)) {
            $className = $classMatch[1];
        }

        preg_match_all('/(?:private|protected|public)\s+static\s+(\??[A-Za-z_\\\\][A-Za-z0-9_\\\\]*)?\s*\$(\w+)/m', $content, $matches, PREG_SET_ORDER);

        $properties = [];

        foreach ($matches as $match) {
            $typeName = $match[1];
            $propName = $match[2];

            // Skip immutable types
            if (in_array($typeName, ['string', 'int', 'float', 'bool'], true)) {
                continue;
            }

            // Check if class has reset mechanism
            $hasReset = preg_match('/public\s+static\s+function\s+(reset|resetInstance|clear|resetAll)\s*\(/m', $content) === 1
                || preg_match('/public\s+function\s+(reset|resetInstance|clear|resetAll)\s*\(/m', $content) === 1;

            $properties[] = [
                'name' => $propName,
                'type' => $typeName,
                'hasReset' => $hasReset,
            ];
        }

        return [
            'namespace' => $namespace,
            'class' => $className,
            'properties' => $properties,
        ];
    }

    #[Test]
    public function no_unreset_static_mutable_state(): void
    {
        $iterator = new \RecursiveDirectoryIterator($this->identityPath);
        $recursive = new \RecursiveIteratorIterator($iterator, \RecursiveIteratorIterator::LEAVES_ONLY);

        $violations = [];

        foreach ($recursive as $file) {
            if (!$file->isFile() || $file->getExtension() !== 'php') {
                continue;
            }

            $result = $this->scanFileForStaticState($file->getPathname());

            foreach ($result['properties'] as $prop) {
                if (!$prop['hasReset']) {
                    $violations[] = sprintf(
                        '%s\\%s::$%s (%s) has no reset mechanism',
                        $result['namespace'],
                        $result['class'],
                        $prop['name'],
                        $prop['type'],
                    );
                }
            }
        }

        $this->assertEmpty(
            $violations,
            "Classes with static mutable state must have a reset method:\n" . implode("\n", $violations),
        );
    }

    #[Test]
    public function static_state_classification_report(): void
    {
        $iterator = new \RecursiveDirectoryIterator($this->identityPath);
        $recursive = new \RecursiveIteratorIterator($iterator, \RecursiveIteratorIterator::LEAVES_ONLY);

        $classified = [];

        foreach ($recursive as $file) {
            if (!$file->isFile() || $file->getExtension() !== 'php') {
                continue;
            }

            $result = $this->scanFileForStaticState($file->getPathname());

            foreach ($result['properties'] as $prop) {
                $classified[] = sprintf(
                    '%s\\%s::$%s (%s) — reset=%s',
                    $result['namespace'],
                    $result['class'],
                    $prop['name'],
                    $prop['type'],
                    $prop['hasReset'] ? 'yes' : 'NO',
                );
            }
        }

        $this->assertGreaterThan(
            0,
            count($classified),
            'Static state scan should find at least one class with static mutable state.',
        );
    }
}
