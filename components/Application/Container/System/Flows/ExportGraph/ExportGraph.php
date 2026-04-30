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
    public function __construct(private ServiceResolver $serviceResolver)
    {
    }

    /**
     * @param array<string, mixed> $context
     *
     * @throws ReflectionException
     */
    public function debugGraph(?string $id = null, array $context = []) : array
    {
        $id ??= '';
        if ($context === []) {
            return $this->serviceResolver->debugGraph(id: $id);
        }

        return $this->serviceResolver->debugGraphInContext(id: $id, context: $context);
    }

    /**
     * @param array<string, mixed> $context
     */
    public function export(?string $format = null, ?string $kind = null, ?string $id = null, array $context = []) : string
    {
        $format ??= 'json';
        $kind   ??= 'dependency';
        $id     ??= '';
        if ($context === []) {
            return $this->serviceResolver->exportGraph(format: $format, kind: $kind, id: $id);
        }

        return $this->serviceResolver->exportGraphInContext(
            format : $format,
            kind   : $kind,
            id     : $id,
            context: $context,
        );
    }

    /**
     * @param array<string, mixed> $context
     */
    public function diff(?string $format = null, ?string $id = null, array $context = []) : string
    {
        $format ??= 'json';
        $id     ??= '';
        if ($context === []) {
            return $this->serviceResolver->diffGraph(format: $format, id: $id);
        }

        return $this->serviceResolver->diffGraphInContext(format: $format, id: $id, context: $context);
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
            return $this->serviceResolver->why(id: $id);
        }

        return $this->serviceResolver->whyInContext(id: $id, context: $context);
    }

    /**
     * @param array<string, mixed> $context
     *
     * @return array<string, mixed>
     * @throws ReflectionException
     */
    public function whoUses(string $id, array $context = []) : array
    {
        if ($context === []) {
            return $this->serviceResolver->whoUses(id: $id);
        }

        return $this->serviceResolver->whoUsesInContext(id: $id, context: $context);
    }

    /**
     * @param array<string, mixed> $context
     *
     * @return array<string, mixed>
     * @throws ReflectionException
     */
    public function whatBreaksIf(string $id, array $context = []) : array
    {
        if ($context === []) {
            return $this->serviceResolver->whatBreaksIf(id: $id);
        }

        return $this->serviceResolver->whatBreaksIfInContext(id: $id, context: $context);
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
            return $this->serviceResolver->showOwner(id: $id);
        }

        return $this->serviceResolver->showOwnerInContext(id: $id, context: $context);
    }

    /**
     * @param array<string, mixed> $context
     *
     * @return array<string, mixed>
     */
    public function showSlice(?string $slice = null, array $context = []) : array
    {
        $slice ??= '';
        if ($context === []) {
            return $this->serviceResolver->debugSlice(slice: $slice);
        }

        return $this->serviceResolver->debugSliceInContext(slice: $slice, context: $context);
    }
}
