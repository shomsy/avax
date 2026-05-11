<?php

declare(strict_types=1);

namespace Avax\Components\Application\Container\System\Capabilities\Composition\Testing;

use Avax\Components\Application\Container\System\Capabilities\Declaration\Bindings\DependencyRegistration;
use Avax\Components\Application\Container\System\Container;
use Avax\Components\Application\Container\System\Flows\CreateContainer\CreateContainer;

/**
 * Composes isolated test containers with ownership-aware defaults.
 */
final readonly class TestComposition
{
    private Container $container;

    private function __construct(
        Container $container,
    ) {
        $this->container = $container;
    }

    public static function create(): self
    {
        return new self(
            container: new CreateContainer()->create(),
        );
    }

    public function container(): Container
    {
        return $this->container;
    }

    public function bindFlow(
        string $slice,
        string $abstract,
        mixed $concrete = null, bool|null $entry = null,
        array $imports = [],
    ): DependencyRegistration {
        $entry ??= false;
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
        mixed $concrete = null,
        bool $exported = true,
    ): DependencyRegistration {
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
        mixed $concrete = null,
        bool $exported = false,
    ): DependencyRegistration {
        $registration = $this->container->bind(abstract: $abstract, concrete: $concrete)
            ->asCapability(ownerSlice: $slice)
            ->asShared()
            ->provenance(provenance: 'TestComposition');

        if ($exported) {
            $registration->export();
        }

        return $registration;
    }

    public function bindConfiguration(string $slice, string $abstract, mixed $concrete = null): DependencyRegistration
    {
        return $this->container->bind(abstract: $abstract, concrete: $concrete)
            ->asConfiguration(ownerSlice: $slice)
            ->asInternal()
            ->provenance(provenance: 'TestComposition');
    }

    public function bindFoundation(string $slice, string $abstract, mixed $concrete = null): DependencyRegistration
    {
        return $this->container->bind(abstract: $abstract, concrete: $concrete)
            ->asFoundation(ownerSlice: $slice)
            ->asInternal()
            ->provenance(provenance: 'TestComposition');
    }

    public function override(string $abstract, mixed $concrete, string $source = 'test-double'): DependencyRegistration
    {
        return $this->container->bind(abstract: $abstract, concrete: $concrete)
            ->overrideSource(source: $source)
            ->provenance(provenance: 'TestComposition')
            ->because(reason: 'test override');
    }

    /**
     * @return array<string, mixed>
     */
    public function snapshot(string $serviceId = ''): array
    {
        return $this->container->debugGraph(id: $serviceId);
    }
}
