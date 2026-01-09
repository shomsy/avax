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
 * @see docs_md/Core/Kernel/Steps/AnalyzePrototypeStep.md#quick-summary
 */
final readonly class AnalyzePrototypeStep implements KernelStep
{
    public function __construct(
        private ServicePrototypeFactory $prototypeFactory,
        private bool                    $strictMode = false
    ) {}

    /**
     * Execute reflection analysis and cache the prototype metadata on the context.
     *
     * @param KernelContext $context
     * @return void
     * @throws \Throwable
     * @see docs_md/Core/Kernel/Steps/AnalyzePrototypeStep.md#method-__invokekernelcontext-context
     */
    public function __invoke(KernelContext $context) : void
    {
        // Check if prototype is already set
        if ($context->hasMeta('analysis', 'prototype')) {
            return;
        }

        /** @var ServiceDefinition|null $definition */
        $definition = $context->getMeta('definition', 'instance');

        // Determine the class to analyze
        $classToAnalyze = $this->determineClassToAnalyze($context->serviceId, $definition);

        if ($classToAnalyze === null || ! class_exists($classToAnalyze)) {
            // Not a class, skip reflection-based prototype analysis
            return;
        }

        try {
            $prototype = $this->prototypeFactory->createFor(class: $classToAnalyze);
            $context->setMeta('analysis', 'prototype', $prototype);
            $context->setMeta('analysis', 'completed_at', microtime(true));
        } catch (Throwable $e) {
            $context->setMeta('analysis', 'failed', true);
            $context->setMeta('analysis', 'error', $e->getMessage());
            throw $e;
        }
    }

    /**
     * Determine the concrete class that should be reflected for this service.
     *
     * @param string                   $serviceId
     * @param ServiceDefinition|null   $definition
     * @return string|null
     * @see docs_md/Core/Kernel/Steps/AnalyzePrototypeStep.md#method-determineclasstoanalyze-string-serviceid-servicedefinition-null-string-null
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
            return get_class($definition->concrete);
        }

        return $serviceId;
    }
}
