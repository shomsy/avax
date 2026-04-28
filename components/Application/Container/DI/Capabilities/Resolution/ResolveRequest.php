<?php

declare(strict_types=1);

namespace Avax\Components\Application\Container\DI\Capabilities\Resolution;

/**
 * Carries one service resolution request and its local context.
 */
final readonly class ResolveRequest
{
    public string|null $consumer;
    public bool        $manualInjection;
    public self|null   $parent;
    public array       $context;
    public array       $overrides;
    public string      $serviceId;

    /**
     * @param array<string, mixed> $overrides
     */
    public function __construct(
        string      $serviceId,
        array|null  $overrides = null,
        array|null  $context = null,
        self|null   $parent = null,
        bool|null   $manualInjection = null,
        string|null $consumer = null
    )
    {
        $overrides             ??= [];
        $context               ??= [];
        $manualInjection       ??= false;
        $this->serviceId       = $serviceId;
        $this->overrides       = $overrides;
        $this->context         = $context;
        $this->parent          = $parent;
        $this->manualInjection = $manualInjection;
        $this->consumer        = $consumer;
    }

    /**
     * @param array<string, mixed> $overrides
     */
    public function child(string $serviceId, array $overrides = []) : self
    {
        return new self(
            serviceId      : $serviceId,
            overrides      : $overrides,
            context        : $this->context,
            parent         : $this,
            manualInjection: $this->manualInjection,
            consumer       : $this->serviceId
        );
    }

    /**
     * Returns a copy with one updated resolution context.
     *
     * @param array<string, mixed> $context
     */
    public function withContext(array $context) : self
    {
        return new self(
            serviceId      : $this->serviceId,
            overrides      : $this->overrides,
            context        : $context,
            parent         : $this->parent,
            manualInjection: $this->manualInjection,
            consumer       : $this->consumer
        );
    }

    /**
     * Returns whether one service id already exists in the parent request chain.
     */
    public function contains(string $serviceId) : bool
    {
        $current = $this->parent;
        while ( $current !== null ) {
            if ($current->serviceId === $serviceId) {
                return true;
            }
            $current = $current->parent;
        }

        return false;
    }

    /**
     * Returns the full dependency path for diagnostics.
     */
    public function getPath() : string
    {
        $path = $this->parent?->getPath() ?? '';

        return ($path !== '' ? $path . ' -> ' : '') . $this->serviceId;
    }
}
