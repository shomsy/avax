<?php

declare(strict_types=1);

namespace Avax\Container\Core\Kernel\Steps;

use Avax\Container\Core\Kernel\Contracts\KernelContext;
use Avax\Container\Core\Kernel\Contracts\KernelStep;
use Avax\Container\Features\Define\Store\ServiceDefinition;
use Avax\Container\Features\Think\Prototype\ServicePrototypeFactory;
use Closure;
use Throwable;

/**
 * Analyze Prototype Step - Dependency Analysis and Preparation
 *
 * This step performs reflection-based analysis of the service class to build
 * a prototype blueprint, which is then used by subsequent steps for injection.
 *
 * @see     docs/Core/Kernel/Steps/AnalyzePrototypeStep.md#quick-summary
 */
final readonly class AnalyzePrototypeStep implements KernelStep
{
    /**
     * @param ServicePrototypeFactory $prototypeFactory Factory for creating class prototypes.
     * @param bool                    $strictMode       Whether to enforce strict validation.
     *
     * @see docs/Core/Kernel/Steps/AnalyzePrototypeStep.md#method-__construct
     */
    public function __construct(
        private ServicePrototypeFactory $prototypeFactory,
        private bool                    $strictMode = false
    ) {}

    /**
     * Execute reflection analysis and cache the prototype metadata on the context.
     *
     * @param KernelContext $context The resolution context.
     *
     * @throws \Throwable If analysis fails.
     *
     * @see docs/Core/Kernel/Steps/AnalyzePrototypeStep.md#method-__invoke
     */
    public function __invoke(KernelContext $context) : void
    {
        // Check if prototype is already set
        if ($context->hasMeta(namespace: 'analysis', key: 'prototype')) {
            return;
        }

        /** @var ServiceDefinition|null $definition */
        $definition = $context->getMeta(namespace: 'definition', key: 'instance');

        // Determine the class to analyze
        $classToAnalyze = $this->determineClassToAnalyze(serviceId: $context->serviceId, definition: $definition);

        if ($classToAnalyze === null || ! class_exists($classToAnalyze)) {
            // Not a class, skip reflection-based prototype analysis
            return;
        }

        try {
            $prototype = $this->prototypeFactory->createFor(class: $classToAnalyze);
            $context->setMeta(namespace: 'analysis', key: 'prototype', value: $prototype);
            $context->setMeta(namespace: 'analysis', key: 'completed_at', value: microtime(as_float: true));
        } catch (Throwable $e) {
            $context->setMeta(namespace: 'analysis', key: 'failed', value: true);
            $context->setMeta(namespace: 'analysis', key: 'error', value: $e->getMessage());
            throw $e;
        }
    }

    /**
     * Determine the concrete class that should be reflected for this service.
     *
     * @param string                 $serviceId  The abstract service ID.
     * @param ServiceDefinition|null $definition The service definition if available.
     *
     * @return string|null The class name or null if non-reflectable.
     *
     * @see docs/Core/Kernel/Steps/AnalyzePrototypeStep.md#method-determineclasstoanalyze
     */
    private function determineClassToAnalyze(string $serviceId, ServiceDefinition|null $definition) : string|null
    {
        if ($definition === null) {
            return $serviceId;
        }

        if ($definition->concrete instanceof Closure) {
            return null; // Closures don't need reflection-based prototypes
        }

        if (is_string($definition->concrete) && $definition->concrete !== '') {
            return $definition->concrete;
        }

        if (is_object($definition->concrete)) {
            return $definition->concrete::class;
        }

        return $serviceId;
    }
}
