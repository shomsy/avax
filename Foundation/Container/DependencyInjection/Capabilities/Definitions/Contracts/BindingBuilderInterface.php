<?php

declare(strict_types=1);

namespace Avax\Container\DependencyInjection\Capabilities\Definitions\Contracts;

/**
 * Fluent write-side configuration for one binding.
 */
interface BindingBuilderInterface
{
    public function to(string|callable|null $concrete) : self;

    public function tag(string|array $tags) : self;

    public function withArguments(array $arguments) : self;

    public function withArgument(string $name, mixed $value) : self;
}
