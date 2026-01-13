<?php

declare(strict_types=1);

namespace Avax\Container\Core\Kernel\Steps;

use Avax\Container\Core\Kernel\Contracts\KernelContext;
use Avax\Container\Core\Kernel\Contracts\KernelStep;
use Avax\Container\Features\Actions\Invoke\Core\InvokeAction;
use ReflectionClass;
use Throwable;

/**
 * Invoke Post Construct Step - Lifecycle Hook Execution
 *
 * This step identifies and executes conventional initialization methods
 * (e.g., init, setup, postConstruct) on the resolved instance after
 * all dependencies have been injected.
 *
 * @see     docs/Core/Kernel/Steps/InvokePostConstructStep.md#quick-summary
 */
final readonly class InvokePostConstructStep implements KernelStep
{
    /**
     * @param InvokeAction $invoker Helper for executing method calls.
     *
     * @see docs/Core/Kernel/Steps/InvokePostConstructStep.md#method-__construct
     */
    public function __construct(
        private InvokeAction $invoker
    ) {}

    /**
     * Invoke conventional post-construct hooks on the resolved instance.
     *
     * @param KernelContext $context The resolution context.
     *
     * @see docs/Core/Kernel/Steps/InvokePostConstructStep.md#method-__invoke
     */
    public function __invoke(KernelContext $context) : void
    {
        if ($context->getMeta(namespace: 'inject', key: 'target', default: false) || $context->getMeta(namespace: 'resolution', key: 'delegated', default: false)) {
            return;
        }

        if ($context->getInstance() === null || ! is_object($context->getInstance())) {
            // Literal values or missing instances skip lifecycle hooks
            return;
        }

        try {
            $class = new ReflectionClass(objectOrClass: $context->getInstance());

            // Common initialization method names
            $initMethods = ['init', 'initialize', 'setup', 'postConstruct'];

            foreach ($initMethods as $methodName) {
                if ($class->hasMethod(name: $methodName) && $class->getMethod(name: $methodName)->isPublic()) {
                    try {
                        $this->invoker->invoke(target: [$context->getInstance(), $methodName]);
                        $context->setMeta(namespace: 'lifecycle', key: 'methods', value: [...($context->getMeta(namespace: 'lifecycle', key: 'methods') ?? []), $methodName]);
                    } catch (Throwable $e) {
                        $context->setMeta(namespace: 'lifecycle', key: 'post_construct_error', value: $e);
                        $context->setMeta(namespace: 'lifecycle', key: 'errors', value: [...($context->getMeta(namespace: 'lifecycle', key: 'errors') ?? []), $methodName => $e->getMessage()]);
                    }
                }
            }

            $context->setMeta(namespace: 'lifecycle', key: 'invoked', value: true);
            $context->setMeta(namespace: 'lifecycle', key: 'completed_at', value: microtime(as_float: true));
        } catch (Throwable) {
            // If we can't reflect it, skip (shouldn't happen with is_object check but good for safety)
        }
    }
}
