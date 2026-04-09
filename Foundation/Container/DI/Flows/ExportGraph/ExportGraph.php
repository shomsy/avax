<?php

declare(strict_types=1);

namespace Avax\Container\DI\Flows\ExportGraph;

use Avax\Container\DI\Capabilities\Resolution\ServiceResolver;

/**
 * Public graph export and impact-analysis flow.
 */
final readonly class ExportGraph
{
    public function __construct(
        private ServiceResolver $resolver
    ) {}

    /**
     * @param array<string, mixed> $context
     */
    public function debugGraph(string $id = '', array $context = []) : array
    {
        if ($context === []) {
            return $this->resolver->debugGraph(id: $id);
        }

        return $this->resolver->debugGraphInContext(id: $id, context: $context);
    }

    /**
     * @param array<string, mixed> $context
     */
    public function export(string $format = 'json', string $kind = 'dependency', string $id = '', array $context = []) : string
    {
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
    public function diff(string $format = 'json', string $id = '', array $context = []) : string
    {
        if ($context === []) {
            return $this->resolver->diffGraph(format: $format, id: $id);
        }

        return $this->resolver->diffGraphInContext(format: $format, id: $id, context: $context);
    }

    /**
     * @param array<string, mixed> $context
     * @return array<string, mixed>
     */
    public function why(string $id, array $context = []) : array
    {
        if ($context === []) {
            return $this->resolver->why(id: $id);
        }

        return $this->resolver->whyInContext(id: $id, context: $context);
    }

    /**
     * @param array<string, mixed> $context
     * @return array<string, mixed>
     */
    public function whoUses(string $id, array $context = []) : array
    {
        if ($context === []) {
            return $this->resolver->whoUses(id: $id);
        }

        return $this->resolver->whoUsesInContext(id: $id, context: $context);
    }

    /**
     * @param array<string, mixed> $context
     * @return array<string, mixed>
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
     * @return array<string, mixed>
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
     * @return array<string, mixed>
     */
    public function showSlice(string $slice = '', array $context = []) : array
    {
        if ($context === []) {
            return $this->resolver->debugSlice(slice: $slice);
        }

        return $this->resolver->debugSliceInContext(slice: $slice, context: $context);
    }
}
