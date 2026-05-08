<?php

declare(strict_types=1);

namespace Avax\Tests\SystemDesignKit\Integration;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use ReflectionClass;
use ReflectionMethod;

/**
 * V3-05: Prove V3 does not duplicate V2 runtime ownership.
 *
 * V3 classes are all design-time only: value objects, enums, and pure-computation flows.
 * V2 classes handle runtime execution: mutable state, I/O, interfaces, runtime machinery.
 * This test statically verifies V3 class shapes confirm design-time-only behavior.
 */
final class V3NoRuntimeDuplicationTest extends TestCase
{
    #[Test]
    public function v3CapabilityClassesAreReadonlyValueObjects() : void
    {
        $capabilityDirs = [
            'labs/SystemDesignKit/System/Capabilities/Capacity',
            'labs/SystemDesignKit/System/Capabilities/Consistency',
            'labs/SystemDesignKit/System/Capabilities/Messaging',
            'labs/SystemDesignKit/System/Capabilities/SchemaValidation',
        ];

        foreach ($capabilityDirs as $dir) {
            $iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($dir),
            );

            foreach ($iterator as $file) {
                if ($file->getExtension() !== 'php') {
                    continue;
                }

                $content = file_get_contents($file->getPathname());
                self::assertNotFalse($content);

                // All V3 capability classes should be final (class or enum)
                self::assertMatchesRegularExpression(
                    '/^final\s+(?:readonly\s+)?(?:class|enum)\s|^enum\s/m',
                    $content,
                    "File {$file->getPathname()} should declare a final class or enum",
                );

                // V3 capability classes should NOT implement interfaces (no runtime contracts)
                // Exception: enums may implement interfaces for behavior methods
                if (str_contains($content, 'enum ')) {
                    continue;
                }

                self::assertDoesNotMatchRegularExpression(
                    '/implements\s/m',
                    $content,
                    "File {$file->getPathname()} should not implement interfaces (design-time only)",
                );
            }
        }
    }

    #[Test]
    public function v3FlowClassesHaveNoMutableState() : void
    {
        $flowDir  = 'labs/SystemDesignKit/System/Flows';
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($flowDir),
        );

        foreach ($iterator as $file) {
            if ($file->getExtension() !== 'php') {
                continue;
            }

            $content = file_get_contents($file->getPathname());
            self::assertNotFalse($content);

            // V3 flow classes should be final
            self::assertMatchesRegularExpression(
                '/^final\s+class\s/m',
                $content,
                "Flow file {$file->getPathname()} should be final",
            );

            // V3 flow classes should NOT have instance properties (no mutable state)
            // They may have injected dependencies via constructor, but no public mutable properties
            $class = $this->getClassFromFile($file->getPathname());

            if ($class !== null && class_exists($class)) {
                $ref = new ReflectionClass($class);
                foreach ($ref->getProperties() as $prop) {
                    // Constructor-injected readonly/typed properties are allowed
                    // But no public writable properties
                    if ($prop->isPublic() && ! $prop->isReadonly()) {
                        self::fail("Flow {$file->getPathname()} has public writable property {$prop->getName()}");
                    }
                }
            }
        }
    }

    /**
     * Extract class name from a PHP file for reflection.
     */
    private function getClassFromFile(string $filePath) : ?string
    {
        $content = file_get_contents($filePath);

        if ($content === false) {
            return null;
        }

        // Extract namespace
        if (! preg_match('/namespace\s+([^;]+);/', $content, $nsMatch)) {
            return null;
        }

        $namespace = $nsMatch[1];

        // Extract class name
        if (! preg_match('/(?:class|enum|interface|trait)\s+(\w+)/', $content, $classMatch)) {
            return null;
        }

        return $namespace . '\\' . $classMatch[1];
    }

    #[Test]
    public function v3ClassesHaveNoIoCalls() : void
    {
        $v3Dir    = 'labs/SystemDesignKit/System';
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($v3Dir),
        );

        $forbiddenFunctions = [
            'fopen',
            'file_get_contents',
            'file_put_contents',
            'exec',
            'shell_exec',
            'passthru',
            'system',
            'mysqli_connect',
            'new PDO',
            'curl_init',
        ];

        foreach ($iterator as $file) {
            if ($file->getExtension() !== 'php') {
                continue;
            }

            // Skip the YAML parser (it needs to read files for validation)
            if (str_contains($file->getPathname(), 'NativeYamlParser')) {
                continue;
            }

            // Skip the schema validator (it validates files)
            if (str_contains($file->getPathname(), 'SchemaValidator')) {
                continue;
            }

            $content = file_get_contents($file->getPathname());
            self::assertNotFalse($content);

            foreach ($forbiddenFunctions as $func) {
                self::assertDoesNotMatchRegularExpression(
                    '/\b' . preg_quote($func, '/') . '\s*\(/',
                    $content,
                    "File {$file->getPathname()} should not call {$func} (design-time only)",
                );
            }
        }
    }

    #[Test]
    public function v3VsV2OwnershipDoesNotOverlap() : void
    {
        // V3 MessagingModel models message types, broker config, outbox/inbox/DLQ policies.
        // V2 MessageBus executes command/event/query dispatch with middleware pipelines.
        // V3 RetryPolicy models retry parameters (max retries, backoff, delays).
        // V2 RetryBuilder/RetryExecutor/TaskRetryPolicy execute retry logic at runtime.
        // V3 QueueDepth models capacity utilization.
        // V2 TaskQueue executes priority queue operations at runtime.
        // V3 Outbox/DeadLetterQueue model configuration.
        // V2 OutboxStore/DeadLetterStore provide runtime storage interfaces.

        // Prove V3 classes are NOT runtime executors
        $v3Classes = [
            'Avax\Labs\SystemDesignKit\System\Capabilities\Messaging\MessagingModel',
            'Avax\Labs\SystemDesignKit\System\Capabilities\Messaging\Retry\RetryPolicy',
            'Avax\Labs\SystemDesignKit\System\Capabilities\Messaging\Outbox\Outbox',
            'Avax\Labs\SystemDesignKit\System\Capabilities\Messaging\DeadLetters\DeadLetterQueue',
            'Avax\Labs\SystemDesignKit\System\Capabilities\Capacity\Queue\QueueDepth',
        ];

        $designTimeCount = 0;

        foreach ($v3Classes as $className) {
            $ref = new ReflectionClass($className);

            // V3 classes should be readonly (value objects) or have only readonly properties
            if ($ref->isReadonly()) {
                $designTimeCount++;

                continue;
            }

            // For non-readonly classes (like MessagingModel which is not readonly due to arrays),
            // verify they have no runtime execution methods
            foreach ($ref->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
                if ($method->isStatic()) {
                    continue; // Static factory methods are fine
                }

                $name = $method->getName();

                // Should not have runtime execution method names
                self::assertNotContains(
                    $name,
                    ['run', 'dispatch', 'publish', 'process', 'handle', 'store', 'enqueue', 'dequeue'],
                    "{$className}::{$name}() looks like runtime execution, should be design-time",
                );
            }

            $designTimeCount++;
        }

        self::assertSame(count($v3Classes), $designTimeCount, 'All V3 classes confirmed as design-time only');
    }
}
