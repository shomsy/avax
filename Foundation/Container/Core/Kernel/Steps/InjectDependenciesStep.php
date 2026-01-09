<?php

declare(strict_types=1);

namespace Avax\Container\Core\Kernel\Steps;

use Avax\Container\Core\Kernel\Contracts\KernelContext;
use Avax\Container\Core\Kernel\Contracts\KernelStep;
use Avax\Container\Features\Actions\Inject\InjectDependencies;

/**
 * Inject Dependencies Step - Property and Method Injection
 *
 * @see docs_md/Core/Kernel/Steps/InjectDependenciesStep.md#quick-summary
 */
final readonly class InjectDependenciesStep implements KernelStep
{
    /**
     * @param InjectDependencies $injector Injection action
     *
     * @see docs_md/Core/Kernel/Steps/InjectDependenciesStep.md#method-__construct
     */
    public function __construct(
        private InjectDependencies $injector
    ) {}

    /**
     * Perform property and method injection on the resolved instance.
     *
     * @param KernelContext $context
     * @return void
     * @throws \ReflectionException
     * @see docs_md/Core/Kernel/Steps/InjectDependenciesStep.md#method-__invoke
     */
    public function __invoke(KernelContext $context) : void
    {
        if ($context->getMeta('resolution', 'delegated', false) || $context->manualInjection) {
            return;
        }

        if ($context->getInstance() === null || ! is_object($context->getInstance())) {
            // Literal values or missing instances skip injection
            return;
        }

        // Perform injection on the resolved instance, passing context to maintain chain
        $injectedInstance = $this->injector->execute(
            target : $context->getInstance(),
            context: $context
        );

        // Update context with injected instance using safe overwrite
        $context->overwriteWith(instance: $injectedInstance);

        // Add injection metadata
        $context->setMeta('inject', 'performed', true);
        $context->setMeta('inject', 'time', microtime(true));
    }
}
