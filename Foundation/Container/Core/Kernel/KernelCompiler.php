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
 * @see docs/Core/Kernel/KernelCompiler.md#quick-summary
 */
final readonly class KernelCompiler
{
    /**
     * Initialize compiler with core components.
     *
     * @param DefinitionStore         $definitions      Service definitions to compile
     * @param ServicePrototypeFactory $prototypeFactory Factory for creating prototypes
     * @param CollectMetrics|null     $metrics          Optional metrics collector
     * @see docs/Core/Kernel/KernelCompiler.md#method-__construct
     */
    public function __construct(
        private DefinitionStore         $definitions,
        private ServicePrototypeFactory $prototypeFactory,
        private CollectMetrics|null     $metrics = null
    ) {}

    /**
     * Compile all service definitions and return statistics.
     *
     * @return array{
     *     compiled_services: int,
     *     cache_size: int,
     *     compilation_time: float,
     *     validation_errors: int
     * } Compilation statistics and results
     * @throws \Throwable When prototype creation or validation fails (handled internally)
     * @see docs/Core/Kernel/KernelCompiler.md#method-compile
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
     * @param ServiceDefinition $definition Service definition to resolve
     * @return string|null Resolved class name or null if not resolvable
     * @throws \Throwable When reflection analysis fails
     * @see docs/Core/Kernel/KernelCompiler.md#method-resolvedefinitionclass
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
                ->getAnalyzer()
                ->getTypeAnalyzer()

                ->reflectClass(className: $candidate);
        } catch (Throwable) {
            return null;
        }

        return $reflection->isInstantiable() ? $candidate : null;
    }

    /**
     * Validate all service definitions without compilation.
     *
     * @return void
     * @throws \Avax\Container\Features\Core\Exceptions\ResolutionException If validation fails
     * @see docs/Core/Kernel/KernelCompiler.md#method-validate
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
     * @return void
     * @see docs/Core/Kernel/KernelCompiler.md#method-clearcache
     */
    public function clearCache(): void
    {
        $this->prototypeFactory->getCache()->clear();
    }

    /**
     * Get compilation statistics with fallback defaults.
     *
     * @param array|null $compilationStats Existing stats or null for defaults
     * @return array Compilation statistics
     * @see docs/Core/Kernel/KernelCompiler.md#method-stats
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
