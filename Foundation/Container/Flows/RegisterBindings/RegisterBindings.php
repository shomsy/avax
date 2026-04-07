<?php

declare(strict_types=1);

namespace Avax\Container\Flows\RegisterBindings;

use Avax\Container\Capabilities\Definitions\Bindings\ContextBuilder;
use Avax\Container\Capabilities\Definitions\Bindings\Registrar;
use Avax\Container\Capabilities\Definitions\Contracts\BindingBuilderInterface;
use Avax\Container\Capabilities\Definitions\Contracts\ContextBuilderInterface;
use Avax\Container\Capabilities\Resolution\Kernel\ContainerKernel;

/**
 * Public write-side flow for registering bindings into the container.
 */
final readonly class RegisterBindings
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
