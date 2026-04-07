<?php

declare(strict_types=1);

namespace Avax\Container\DependencyInjection\Flow\RegisterBindings;

use Avax\Container\DependencyInjection\Capability\Definitions\Bindings\ContextBuilder;
use Avax\Container\DependencyInjection\Capability\Definitions\Bindings\Registrar;
use Avax\Container\DependencyInjection\Capability\Definitions\Contracts\BindingBuilderInterface;
use Avax\Container\DependencyInjection\Capability\Definitions\Contracts\ContextBuilderInterface;
use Avax\Container\DependencyInjection\Capability\Definitions\Contracts\RegistryInterface;
use Avax\Container\DependencyInjection\Capability\Resolution\Kernel\ContainerKernel;

/**
 * Public write-side flow for registering bindings into the container.
 */
final readonly class RegisterBindings implements RegistryInterface
{
    public function __construct(
        private ContainerKernel $kernel
    ) {}

    public function bind(string $abstract, mixed $concrete = null) : BindingBuilderInterface
    {
        return $this->registrar()->bind(abstract: $abstract, concrete: $concrete);
    }

    public function singleton(string $abstract, mixed $concrete = null) : BindingBuilderInterface
    {
        return $this->registrar()->singleton(abstract: $abstract, concrete: $concrete);
    }

    public function scoped(string $abstract, mixed $concrete = null) : BindingBuilderInterface
    {
        return $this->registrar()->scoped(abstract: $abstract, concrete: $concrete);
    }

    public function when(string $consumer) : ContextBuilderInterface
    {
        return new ContextBuilder(store: $this->kernel->definitions(), consumer: $consumer);
    }

    public function extend(string $abstract, callable $closure) : void
    {
        $this->registrar()->extend(abstract: $abstract, closure: $closure);
    }

    public function tag(string|array $abstracts, string|array $tags) : void
    {
        $this->registrar()->tag(abstracts: $abstracts, tags: $tags);
    }

    public function instance(string $abstract, object $instance) : void
    {
        $this->kernel->instance(abstract: $abstract, instance: $instance);
    }

    private function registrar() : Registrar
    {
        return new Registrar(definitions: $this->kernel->definitions());
    }
}
