<?php

declare(strict_types=1);

namespace Avax\Container\Core\Kernel\Steps;

use Avax\Container\Core\Kernel\Contracts\KernelContext;
use Avax\Container\Core\Kernel\Contracts\KernelStep;
use Avax\Container\Features\Core\Enum\ServiceLifetime;
use Avax\Container\Features\Core\Exceptions\ResolutionException;
use Avax\Container\Features\Define\Store\DefinitionStore;
use Avax\Container\Features\Define\Store\ServiceDefinition;
use ReflectionClass;
use Throwable;

/**
 * Ensure Definition Exists Step - Validates service definition before resolution
 *
 * Ensures that a service definition exists before attempting resolution.
 * Creates an ephemeral definition if auto-define is enabled, without polluting
 * the global definition store. Uses Transient as default lifetime for auto-wired classes.
 *
 * @see docs/Core/Kernel/Steps/EnsureDefinitionExistsStep.md#quick-summary
 */
final readonly class EnsureDefinitionExistsStep implements KernelStep
{
    /**
     * @param DefinitionStore $definitions Definition registry
     * @param bool            $autoDefine  Whether to auto-create ephemeral definitions for instantiable classes
     * @param bool            $strictMode  Whether missing definitions should throw immediately
     */
    public function __construct(
        private DefinitionStore $definitions,
        private bool            $autoDefine = false,
        private bool            $strictMode = false,
    ) {}

    /**
     * Ensure a definition exists in the context, optionally auto-defining classes.
     *
     *
     * @throws \Avax\Container\Features\Core\Exceptions\ResolutionException When strict mode is enabled and the
     *                                                                      definition is missing
     *
     * @see docs/Core/Kernel/Steps/EnsureDefinitionExistsStep.md#method-__invoke
     */
    public function __invoke(KernelContext $context) : void
    {
        if ($context->getMeta(namespace: 'inject', key: 'target', default: false)) {
            return;
        }
        if ($this->definitions->has(abstract: $context->serviceId)) {
            // Definition exists in store
            $context->setMeta(namespace: 'definition', key: 'source', value: 'store');
            $context->setMeta(namespace: 'definition', key: 'instance', value: $this->definitions->get(abstract: $context->serviceId));

            return;
        }

        // Definition does not exist
        if ($this->autoDefine && $this->isAutoDefinable(serviceId: $context->serviceId)) {
            // Create ephemeral definition for auto-wiring
            $definition           = new ServiceDefinition(abstract: $context->serviceId);
            $definition->lifetime = ServiceLifetime::Transient; // Default to Transient for auto-wired

            // Store only in context, not in global store
            $context->setMeta(namespace: 'definition', key: 'source', value: 'auto');
            $context->setMeta(namespace: 'definition', key: 'instance', value: $definition);
        } else {
            $context->setMeta(namespace: 'definition', key: 'source', value: 'missing');
            $context->setMeta(namespace: 'definition', key: 'warning', value: "Missing definition for [{$context->serviceId}]. Resolution may fail if not literal.");

            if ($this->strictMode) {
                throw new ResolutionException(
                    message: "Strict Mode: Missing definition for service [{$context->serviceId}]. Auto-define is " . ($this->autoDefine ? 'on but failed' : 'off') . '.'
                );
            }
        }
    }

    /**
     * Check if a service ID can be auto-defined.
     *
     * @param string $serviceId The service identifier
     *
     * @return bool True if auto-definable
     *
     * @see docs/Core/Kernel/Steps/EnsureDefinitionExistsStep.md#method-isautodefinable
     */
    private function isAutoDefinable(string $serviceId) : bool
    {
        // Must be a class name
        if (! class_exists($serviceId)) {
            return false;
        }

        try {
            $reflection = new ReflectionClass(objectOrClass: $serviceId);

            // Must be instantiable (not abstract, not interface)
            return $reflection->isInstantiable();
        } catch (Throwable) {
            return false;
        }
    }
}
