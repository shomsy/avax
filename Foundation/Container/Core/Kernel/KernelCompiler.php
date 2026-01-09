<?php

declare(strict_types=1);

namespace Avax\Container\Core\Kernel;

use Avax\Container\Features\Define\Store\DefinitionStore;
use Avax\Container\Features\Define\Store\ServiceDefinition;
use Avax\Container\Features\Think\Prototype\ServicePrototypeFactory;
use Avax\Container\Features\Think\Verify\VerifyPrototype;
use Avax\Container\Observe\Metrics\CollectMetrics;
use Closure;
use Throwable;

/**
 * Kernel Compiler - Build-time Logic and Validation
 *
 * Handles service compilation, validation, and cache management.
 * Separated from orchestration to keep ContainerKernel focused on runtime, enabling early error detection and performance optimization.
 *
 * @see docs_md/Core/Kernel/KernelCompiler.md#quick-summary
 */
final readonly class KernelCompiler
{
    /**
     * Initialize compiler with core components.
     *
     * @param DefinitionStore $definitions Service definitions to compile
     * @param ServicePrototypeFactory $prototypeFactory Factory for creating prototypes
     * @param CollectMetrics|null $metrics Optional metrics collector
     */
    public function __construct(
        private DefinitionStore         $definitions,
        private ServicePrototypeFactory $prototypeFactory,
        private CollectMetrics|null     $metrics = null
    ) {}

    /**
     * Compile all service definitions and return statistics.
     *
     * Performs full compilation by analyzing all registered services,
     * generating optimized prototypes, and validating configurations.
     * This method serves as the primary entry point for build-time optimization,
     * transforming declarative service definitions into optimized runtime structures.
     *
     * @return array{
     *     compiled_services: int,
     *     cache_size: int,
     *     compilation_time: float,
     *     validation_errors: int
     * } Compilation statistics and results
     * @throws \Throwable When prototype creation or validation fails (handled internally)
     * @see docs_md/Core/Kernel/KernelCompiler.md#method-compile
     */
    public function compile(): array
    {
        $startTime        = microtime(true);
        $compiledServices = 0;
        $validationErrors = 0;
        $verifier         = new VerifyPrototype();

        foreach ($this->definitions->getAllDefinitions() as $definition) {
            try {
                $class = $this->resolveDefinitionClass(definition: $definition);

                if ($class === null) {
                    continue;
                }

                $prototype = $this->prototypeFactory->createFor(class: $class);
                $verifier->validate(prototype: $prototype);
                $compiledServices++;
            } catch (Throwable $e) {
                $validationErrors++;

                // Log validation error in metrics if available
                $this->metrics?->record(event: 'compilation_error', data: [
                    'service'  => $definition->abstract,
                    'concrete' => $definition->concrete,
                    'error'    => $e->getMessage(),
                    'type'     => get_class($e),
                ]);
            }
        }

        return [
            'compiled_services' => $compiledServices,
            'cache_size'        => $this->prototypeFactory->getCache()->count(),
            'compilation_time'  => round(microtime(true) - $startTime, 4),
            'validation_errors' => $validationErrors,
        ];
    }

    /**
     * Resolve class name from service definition.
     *
     * Determines the concrete class to use for a service definition by examining
     * the concrete binding, falling back to the abstract identifier.
     * Validates that the resolved class exists and is instantiable.
     *
     * @param ServiceDefinition $definition Service definition to resolve
     * @return string|null Resolved class name or null if not resolvable
     * @throws \Throwable When reflection analysis fails (handled internally)
     */
    private function resolveDefinitionClass(ServiceDefinition $definition): string|null
    {
        if (is_string($definition->concrete) && $definition->concrete !== '') {
            $candidate = $definition->concrete;
        } elseif (is_object($definition->concrete) && ! ($definition->concrete instanceof Closure)) {
            $candidate = get_class($definition->concrete);
        } elseif ($definition->abstract !== '') {
            $candidate = $definition->abstract;
        } else {
            return null;
        }

        try {
            $reflection = $this->prototypeFactory
                ->getReflectionTypeAnalyzer()
                ->reflectClass(className: $candidate);
        } catch (Throwable) {
            return null;
        }

        return $reflection->isInstantiable() ? $candidate : null;
    }

    /**
     * Validate all service definitions without compilation.
     *
     * Checks all registered services for configuration errors and dependency issues
     * without performing expensive prototype generation and caching.
     * This method provides lightweight validation for development and testing scenarios.
     *
     * @return void
     * @throws \Avax\Container\Features\Core\Exceptions\ResolutionException If validation fails
     * @see docs_md/Core/Kernel/KernelCompiler.md#method-validate
     */
    public function validate(): void
    {
        $verifier = new VerifyPrototype();

        foreach ($this->definitions->getAllDefinitions() as $definition) {
            $class = $this->resolveDefinitionClass(definition: $definition);
            if ($class === null) {
                continue;
            }
            $prototype = $this->prototypeFactory->createFor(class: $class);
            $verifier->validate(prototype: $prototype);
        }
    }

    /**
     * Clear all caches.
     *
     * Removes all cached prototypes and compilation artifacts.
     * Forces fresh analysis on next container operation.
     * This method enables cache invalidation for development workflows and testing.
     *
     * @return void
     * @see docs_md/Core/Kernel/KernelCompiler.md#method-clearCache
     */
    public function clearCache(): void
    {
        $this->prototypeFactory->getCache()->clear();
    }

    /**
     * Get compilation statistics with fallback defaults.
     *
     * Provides compilation metrics, using provided stats or generating defaults
     * based on current cache state.
     * This method enables monitoring and debugging of compilation performance.
     *
     * @param array|null $compilationStats Existing stats or null for defaults
     * @return array Compilation statistics
     * @see docs_md/Core/Kernel/KernelCompiler.md#method-stats
     */
    public function stats(array|null $compilationStats): array
    {
        return $compilationStats ?? [
            'compiled_services' => $this->prototypeFactory->getCache()->count(),
            'cache_size'        => $this->prototypeFactory->getCache()->count(),
            'compilation_time'  => 0.0,
            'validation_errors' => 0,
        ];
    }
}
