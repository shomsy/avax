<?php

declare(strict_types=1);

namespace Avax\Container\Exceptions;

use ReflectionClass;
use ReflectionException;
use RuntimeException;
use Throwable;

/**
 * Thrown when a circular dependency is detected during resolution.
 */
class CircularDependencyException extends RuntimeException
{
    #[\Override]
    public function __construct(
        string         $serviceId,
        array          $resolutionStack,
        int            $code = 0,
        Throwable|null $previous = null
    ) {
        $formattedStack  = implode(separator: ' -> ', array: $resolutionStack);
        $reflectionHints = self::generateDebugHints(resolutionStack: $resolutionStack);
        $suggestions     = self::suggestFix(serviceId: $serviceId);

        $message = <<<TEXT
            ❌ Circular dependency detected while resolving service: '$serviceId'
            🌀 Resolution stack:
              $formattedStack
            
            🔍 Debug hints:
            $reflectionHints
            
            💡 Suggestions:
            $suggestions
            TEXT;

        parent::__construct(message: $message, code: $code, previous: $previous);
    }

    /**
     * Generate reflection-based debug output.
     *
     * @param string[] $resolutionStack
     *
     * @return string
     */
    private static function generateDebugHints(array $resolutionStack) : string
    {
        $lines = [];

        foreach ($resolutionStack as $class) {
            try {
                $r    = new ReflectionClass(objectOrClass: $class);
                $file = $r->getFileName();
                $line = $r->getStartLine();

                $lines[] = "• {$class}  (defined in {$file}:{$line})";
            } catch (ReflectionException) {
                $lines[] = "• {$class}  (could not locate source)";
            }
        }

        return implode(separator: "\n", array: $lines);
    }

    /**
     * Suggest common ways to break circular dependency.
     */
    private static function suggestFix(string $serviceId) : string
    {
        return <<<SUGGEST
            - Use constructor injection only for stable leaf services.
            - Break cycle by:
                • Introducing a factory/service locator for one of the deps.
                • Using an interface or abstract class with deferred resolution.
                • Lazy loading with Closure or `fn() => app()->get(...)`.
            
            - Check if $serviceId is indirectly requiring itself.
            SUGGEST;
    }
}
