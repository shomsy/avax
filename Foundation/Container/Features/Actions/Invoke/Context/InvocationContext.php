<?php

declare(strict_types=1);

namespace Avax\Container\Features\Actions\Invoke\Context;

/**
 * Value object holding state for a single invocation.
 *
 * @see docs_md/Features/Actions/Invoke/Context/InvocationContext.md#quick-summary
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
     * @see docs_md/Features/Actions/Invoke/Context/InvocationContext.md#method-withnormalizedtarget
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
     * @see docs_md/Features/Actions/Invoke/Context/InvocationContext.md#method-withreflection
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
     * @see docs_md/Features/Actions/Invoke/Context/InvocationContext.md#method-withresolvedarguments
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
     * @see docs_md/Features/Actions/Invoke/Context/InvocationContext.md#method-withresult
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
     * @see docs_md/Features/Actions/Invoke/Context/InvocationContext.md#method-geteffectivetarget
     */
    public function getEffectiveTarget() : mixed
    {
        return $this->normalizedTarget ?? $this->originalTarget;
    }
}
