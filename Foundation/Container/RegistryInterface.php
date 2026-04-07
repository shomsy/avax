<?php

declare(strict_types=1);

namespace Avax\Container;

/**
 * Stable public write-side registration contract.
 */
interface RegistryInterface
{
    public function bind(string $abstract, mixed $concrete = null) : BindingBuilderInterface;

    public function singleton(string $abstract, mixed $concrete = null) : BindingBuilderInterface;

    public function scoped(string $abstract, mixed $concrete = null) : BindingBuilderInterface;

    public function instance(string $abstract, object $instance) : void;

    public function extend(string $abstract, callable $closure) : void;

    public function when(string $consumer) : ContextBuilderInterface;

    public function tag(string|array $abstracts, string|array $tags) : void;
}
