<?php

declare(strict_types=1);

namespace Avax\Components\Application\Container\System\Flows\ExportGraph;

use Avax\Components\Application\Container\System\Capabilities\Resolution\ServiceResolver;
use ReflectionException;

/**
 * Public graph export and impact-analysis flow.
 */
final readonly class ExportGraph
{
    private ServiceResolver $resolver;

    public function __construct(
        ServiceResolver $resolver
    )
    {
        $this->resolver = $resolver;
    }

    /**
     * @param array<string, mixed> $context
     *
     * @throws ReflectionException
     */
    public function debugGraph(string|null $id = null, array $context = []) : array
    {
        $id ??= '';
        if ($context === []) {
            return $this->resolver->debugGraph(id: $id);
        }

        return $this->resolver->debugGraphInContext(id: $id, context: $context);
    }

    /**
     * @param array<string, mixed> $context
     */
    public function export(string|null $format = null, string|null $kind = null, string|null $id = null, array $context = []) : string
    {
        $format ??= 'json';
        $kind   ??= 'dependency';
        $id     ??= '';
        if ($context === []) {
            return $this->resolver->exportGraph(format: $format, kind: $kind, id: $id);
        }

        return $this->resolver->exportGraphInContext(
            format : $format,
            kind   : $kind,
            id     : $id,
            context: $context
        );
    }

    /**
     * @param array<string, mixed> $context
     */
    public function diff(string|null $format = null, string|null $id = null, array $context = []) : string
    {
        $format ??= 'json';
        $id     ??= '';
        if ($context === []) {
            return $this->resolver->diffGraph(format: $format, id: $id);
        }

        return $this->resolver->diffGraphInContext(format: $format, id: $id, context: $context);
    }

    /**
     * @param array<string, mixed> $context
     *
     * @return array<string, mixed>
     * @throws ReflectionException
     */
    public function why(string $id, array $context = []) : array
    {
        if ($context === []) {
            return $this->resolver->why(id: $id);
        }

        return $this->resolver->whyInContext(id: $id, context: $context);
    }

    /**
     * @param string               $id
     * @param array<string, mixed> $context
     *
     * @return array<string, mixed>
     * @throws ReflectionException
     */
    public function whoUses(string $id, array $context = []) : array
    {
        if ($context === []) {
            return $this->resolver->whoUses(id: $id);
        }

        return $this->resolver->whoUsesInContext(id: $id, context: $context);
    }

    /**
     * @param string               $id
     * @param array<string, mixed> $context
     *
     * @return array<string, mixed>
     * @throws ReflectionException
     */
    public function whatBreaksIf(string $id, array $context = []) : array
    {
        if ($context === []) {
            return $this->resolver->whatBreaksIf(id: $id);
        }

        return $this->resolver->whatBreaksIfInContext(id: $id, context: $context);
    }

    /**
     * @param array<string, mixed> $context
     *
     * @return array<string, mixed>
     * @throws ReflectionException
     */
    public function showOwner(string $id, array $context = []) : array
    {
        if ($context === []) {
            return $this->resolver->showOwner(id: $id);
        }

        return $this->resolver->showOwnerInContext(id: $id, context: $context);
    }

    /**
     * @param array<string, mixed> $context
     *
     * @return array<string, mixed>
     */
    public function showSlice(string|null $slice = null, array $context = []) : array
    {
        $slice ??= '';
        if ($context === []) {
            return $this->resolver->debugSlice(slice: $slice);
        }

        return $this->resolver->debugSliceInContext(slice: $slice, context: $context);
    }
}
