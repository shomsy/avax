<?php

declare(strict_types=1);

namespace Avax\Container\Core\Kernel\Steps;

use Avax\Container\Core\Kernel\Contracts\KernelContext;
use Avax\Container\Core\Kernel\Contracts\KernelStep;
use Avax\Container\Features\Actions\Inject\InjectDependencies;

/**
 * Inject Dependencies Step - Property and Method Injection
 *
 * This step performs post-instantiation dependency injection on the resolved
 * service, handling properties and methods marked for injection via attributes.
 *
 * @package Avax\Container\Core\Kernel\Steps
 * @see docs_md/Core/Kernel/Steps/InjectDependenciesStep.md#quick-summary
 */
final readonly class InjectDependenciesStep implements KernelStep
{
    /**
     * @param InjectDependencies $injector The dependency injection engine.
     * @see docs_md/Core/Kernel/Steps/InjectDependenciesStep.md#method-__construct
     */
    public function __construct(
        private InjectDependencies $injector
    ) {}

    /**
     * Perform property and method injection on the resolved instance.
     *
     * @param KernelContext $context The resolution context.
     * @return void
     * @throws \ReflectionException If injection fails due to reflection errors.
     * @see docs_md/Core/Kernel/Steps/InjectDependenciesStep.md#method-__invoke
     */
    public function __invoke(KernelContext $context): void
    {
        // Skip if freshly delegated to another service or already injected/cached
        if (
            $context->getMeta('resolution', 'delegated', false) ||
            $context->getMeta('resolution', 'cached', false)
        ) {
            return;
        }

        if ($context->getInstance() === null || ! is_object($context->getInstance())) {
            // Literal values or missing instances skip injection
            return;
        }

        // Perform injection on the resolved instance, passing context to maintain chain
        $injectedInstance = $this->injector->execute(
            target: $context->getInstance(),
            context: $context
        );

        // Update context with injected instance using safe overwrite
        $context->overwriteWith(instance: $injectedInstance);

        // Add injection metadata
        $context->setMeta('inject', 'performed', true);
        $context->setMeta('inject', 'time', microtime(as_float: true));
    }
}
