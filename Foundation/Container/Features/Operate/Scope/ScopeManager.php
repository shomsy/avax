<?php

declare(strict_types=1);

namespace Avax\Container\Features\Operate\Scope;

/**
 * Fluent wrapper for {@see ScopeRegistry}.
 *
 * This class acts as the public-facing scope API used by resolution steps and shutdown actions.
 *
 * @see docs_md/Features/Operate/Scope/ScopeManager.md#quick-summary
 */
final readonly class ScopeManager
{
    /**
     * @param ScopeRegistry $registry Underlying scope storage.
     * @see docs_md/Features/Operate/Scope/ScopeManager.md#method-__construct
     */
    public function __construct(private ScopeRegistry $registry) {}

    /**
     * @see docs_md/Features/Operate/Scope/ScopeManager.md#method-has
     */
    public function has(string $abstract): bool
    {
        return $this->registry->has(abstract: $abstract);
    }

    /**
     * @see docs_md/Features/Operate/Scope/ScopeManager.md#method-get
     */
    public function get(string $abstract): mixed
    {
        return $this->registry->get(abstract: $abstract);
    }

    /**
     * @see docs_md/Features/Operate/Scope/ScopeManager.md#method-set
     */
    public function set(string $abstract, mixed $instance): void
    {
        $this->registry->set(abstract: $abstract, instance: $instance);
    }

    /**
     * @see docs_md/Features/Operate/Scope/ScopeManager.md#method-instance
     */
    public function instance(string $abstract, mixed $instance): void
    {
        $this->set(abstract: $abstract, instance: $instance);
    }

    /**
     * @see docs_md/Features/Operate/Scope/ScopeManager.md#method-setscoped
     */
    public function setScoped(string $abstract, mixed $instance): void
    {
        $this->registry->setScoped(abstract: $abstract, instance: $instance);
    }

    /**
     * @see docs_md/Features/Operate/Scope/ScopeManager.md#method-beginscope
     */
    public function beginScope(): void
    {
        $this->registry->beginScope();
    }

    /**
     * @see docs_md/Features/Operate/Scope/ScopeManager.md#method-endscope
     */
    public function endScope(): void
    {
        $this->registry->endScope();
    }

    /**
     * @see docs_md/Features/Operate/Scope/ScopeManager.md#method-terminate
     */
    public function terminate(): void
    {
        $this->registry->terminate();
    }
}
