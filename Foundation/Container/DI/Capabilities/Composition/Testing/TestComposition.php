<?php

declare(strict_types=1);

namespace Avax\Container\DI\Capabilities\Composition\Testing;

use Avax\Container\DI\Capabilities\Composition\CreateContainerConfig;
use Avax\Container\DI\Capabilities\Declaration\Bindings\ServiceRegistration;
use Avax\Container\DI\Container;
use Avax\Container\DI\Flows\CreateContainer\CreateContainer;

/**
 * Composes isolated test containers with ownership-aware defaults.
 */
final readonly class TestComposition
{
    private function __construct(
        private Container $container
    ) {}

    public static function create(CreateContainerConfig|null $config = null) : self
    {
        return new self(
            container: (new CreateContainer)->create(config: $config)
        );
    }

    public function container() : Container
    {
        return $this->container;
    }

    public function bindFlow(
        string    $slice,
        string    $abstract,
        mixed     $concrete = null,
        bool|null $entry = null,
        array     $imports = []
    ) : ServiceRegistration
    {
        $entry        ??= false;
        $registration = $this->container->bind(abstract: $abstract, concrete: $concrete)
            ->asFlow(ownerSlice: $slice)
            ->asPrivate();

        if ($entry) {
            $registration->entry();
        }

        if ($imports !== []) {
            $registration->import(slices: $imports);
        }

        return $registration->provenance(provenance: 'TestComposition');
    }

    public function singletonCapability(
        string $slice,
        string $abstract,
        mixed  $concrete = null,
        bool   $exported = true
    ) : ServiceRegistration
    {
        $registration = $this->container->singleton(abstract: $abstract, concrete: $concrete)
            ->asCapability(ownerSlice: $slice)
            ->asShared()
            ->provenance(provenance: 'TestComposition');

        if ($exported) {
            $registration->export();
        }

        return $registration;
    }

    public function bindCapability(
        string $slice,
        string $abstract,
        mixed  $concrete = null,
        bool   $exported = false
    ) : ServiceRegistration
    {
        $registration = $this->container->bind(abstract: $abstract, concrete: $concrete)
            ->asCapability(ownerSlice: $slice)
            ->asShared()
            ->provenance(provenance: 'TestComposition');

        if ($exported) {
            $registration->export();
        }

        return $registration;
    }

    public function bindConfiguration(string $slice, string $abstract, mixed $concrete = null) : ServiceRegistration
    {
        return $this->container->bind(abstract: $abstract, concrete: $concrete)
            ->asConfiguration(ownerSlice: $slice)
            ->asInternal()
            ->provenance(provenance: 'TestComposition');
    }

    public function bindFoundation(string $slice, string $abstract, mixed $concrete = null) : ServiceRegistration
    {
        return $this->container->bind(abstract: $abstract, concrete: $concrete)
            ->asFoundation(ownerSlice: $slice)
            ->asInternal()
            ->provenance(provenance: 'TestComposition');
    }

    public function override(string $abstract, mixed $concrete, string $source = 'test-double') : ServiceRegistration
    {
        return $this->container->bind(abstract: $abstract, concrete: $concrete)
            ->overrideSource(source: $source)
            ->provenance(provenance: 'TestComposition')
            ->because(reason: 'test override');
    }

    /**
     * @return array<string, mixed>
     */
    public function snapshot(string $serviceId = '') : array
    {
        return $this->container->debugGraph(id: $serviceId);
    }
}
