<?php

declare(strict_types=1);

namespace Avax\Container\Capabilities\Invocation\CallableInvocation;

/**
 * Value object holding state for a single invocation.
 *
 */
final readonly class InvocationContext
{
    public function __construct(
        public mixed       $originalTarget,
        public mixed       $normalizedTarget = null,
        public object|null $reflection = null,
        public array|null  $resolvedArguments = null,
        public mixed       $result = null,
    ) {}

    /**
     * Create a new context with normalized target.
     *
     */
    public function withNormalizedTarget(mixed $normalizedTarget) : self
    {
        return new self(
            originalTarget   : $this->originalTarget,
            normalizedTarget : $normalizedTarget,
            reflection       : $this->reflection,
            resolvedArguments: $this->resolvedArguments,
            result           : $this->result,
        );
    }

    /**
     * Create a new context with reflection object.
     *
     */
    public function withReflection(object $reflection) : self
    {
        return new self(
            originalTarget   : $this->originalTarget,
            normalizedTarget : $this->normalizedTarget,
            reflection       : $reflection,
            resolvedArguments: $this->resolvedArguments,
            result           : $this->result,
        );
    }

    /**
     * Create a new context with resolved arguments.
     *
     */
    public function withResolvedArguments(array $resolvedArguments) : self
    {
        return new self(
            originalTarget   : $this->originalTarget,
            normalizedTarget : $this->normalizedTarget,
            reflection       : $this->reflection,
            resolvedArguments: $resolvedArguments,
            result           : $this->result,
        );
    }

    /**
     * Create a new context with final result.
     *
     */
    public function withResult(mixed $result) : self
    {
        return new self(
            originalTarget   : $this->originalTarget,
            normalizedTarget : $this->normalizedTarget,
            reflection       : $this->reflection,
            resolvedArguments: $this->resolvedArguments,
            result           : $result,
        );
    }

    /**
     * Get the effective target for current pipeline phase.
     *
     * Returns normalized target if available, otherwise original target.
     * This allows resolvers to work with the most appropriate target format.
     *
     */
    public function getEffectiveTarget() : mixed
    {
        return $this->normalizedTarget ?? $this->originalTarget;
    }
}
