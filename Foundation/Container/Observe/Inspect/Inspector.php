<?php

declare(strict_types=1);
namespace Avax\Container\Observe\Inspect;

use Avax\Container\Features\Define\Store\DefinitionStore;
use Avax\Container\Features\Define\Store\ServiceDefinition;
use Avax\Container\Features\Operate\Scope\ScopeRegistry;
use Avax\Container\Features\Think\Prototype\DependencyInjectionPrototypeFactory;
use Throwable;

/**
 * Service for deep introspection of container state.
 *
 * @see docs/Observe/Inspect/Inspector.md#quick-summary
 */
final readonly class Inspector
{
    /**
     * @param DefinitionStore                     $definitions      Definition store used to check whether a service is defined
     * @param ScopeRegistry                       $scopes           Scope registry used to check whether a service is cached
     * @param DependencyInjectionPrototypeFactory $prototypeFactory Prototype factory used to build reflection-based summaries
     * @see docs/Observe/Inspect/Inspector.md#method-__construct
     */
    public function __construct(
        private DefinitionStore                     $definitions,
        private ScopeRegistry                       $scopes,
        private DependencyInjectionPrototypeFactory $prototypeFactory
    ) {}

    /**
     * @param string $id Service identifier/abstract to inspect
     *
     * @return array Inspection report payload
     * @see docs/Observe/Inspect/Inspector.md#method-inspect
     */
    public function inspect(string $id) : array
    {
        $def         = $this->definitions->get(abstract: $id);
        $hasInstance = $this->scopes->has(abstract: $id);

        $prototype = null;
        try {
            $prototypeDto = $this->prototypeFactory->analyzeReflectionFor(class: $id);
            $prototype    = [
                'implementation'  => $prototypeDto->class,
                'is_instantiable' => $prototypeDto->isInstantiable,
                'dependencies'    => count($prototypeDto->constructor?->parameters ?? [])
            ];
        } catch (Throwable $e) {
            $prototype = ['error' => $e->getMessage()];
        }

        return [
            'id'        => $id,
            'defined'   => $def instanceof ServiceDefinition,
            'cached'    => $hasInstance,
            'lifetime'  => $def?->lifetime->value ?? 'unknown',
            'tags'      => $def?->tags ?? [],
            'prototype' => $prototype,
        ];
    }
}
