<?php

declare(strict_types=1);

namespace Avax\Container\DependencyInjection\Scopes;

final readonly class ManageScopes implements ScopeInterface
{
    public function __construct(private ScopeStore $store) {}

    public function has(string $abstract) : bool
    {
        return $this->store->has(abstract: $abstract);
    }

    public function get(string $abstract) : mixed
    {
        return $this->store->get(abstract: $abstract);
    }

    public function set(string $abstract, mixed $instance) : void
    {
        $this->store->set(abstract: $abstract, instance: $instance);
    }

    public function instance(string $abstract, mixed $instance) : void
    {
        $this->store->share(abstract: $abstract, instance: $instance);
    }

    public function withinScope(callable $callback) : mixed
    {
        $this->open();

        try {
            return $callback();
        } finally {
            $this->close();
        }
    }

    public function beginScope() : void
    {
        $this->open();
    }

    public function endScope() : void
    {
        $this->close();
    }

    public function terminate() : void
    {
        $this->store->terminate();
    }

    public function open() : void
    {
        $this->store->open();
    }

    public function close() : void
    {
        $this->store->close();
    }
}
