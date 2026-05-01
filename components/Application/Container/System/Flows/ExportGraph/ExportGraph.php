<?php

declare(strict_types=1);

namespace Avax\Components\Application\Container\System\Flows\ExportGraph;

use Avax\Components\Application\Container\System\Capabilities\Resolution\ResolveDependency;
use ReflectionException;

/**
 * Public graph export and impact-analysis flow.
 */
final readonly class ExportGraph
{
    public function __construct(private ResolveDependency $resolveDependency)
    {
    }

    /**
     * @param array<string, mixed> $context
     *
     * @throws ReflectionException
     */
    public function debugGraph(string $id = null, array $context = []) : array
    {
        $id ??= '';
        if ($context === []) {
            return $this->resolveDependency->debugGraph(id: $id);
        }

        return $this->resolveDependency->debugGraphInContext(id: $id, context: $context);
    }

    /**
     * @param array<string, mixed> $context
     */
    public function export(string $format = null, string $kind = null, string $id = null, array $context = []) : string
    {
        $format ??= 'json';
        $kind   ??= 'dependency';
        $id     ??= '';
        if ($context === []) {
            return $this->resolveDependency->exportGraph(format: $format, kind: $kind, id: $id);
        }

        return $this->resolveDependency->exportGraphInContext(
            format : $format,
            kind   : $kind,
            id     : $id,
            context: $context,
        );
    }

    /**
     * @param array<string, mixed> $context
     */
    public function diff(string $format = null, string $id = null, array $context = []) : string
    {
        $format ??= 'json';
        $id     ??= '';
        if ($context === []) {
            return $this->resolveDependency->diffGraph(format: $format, id: $id);
        }

        return $this->resolveDependency->diffGraphInContext(format: $format, id: $id, context: $context);
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
            return $this->resolveDependency->why(id: $id);
        }

        return $this->resolveDependency->whyInContext(id: $id, context: $context);
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
            return $this->resolveDependency->whoUses(id: $id);
        }

        return $this->resolveDependency->whoUsesInContext(id: $id, context: $context);
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
            return $this->resolveDependency->whatBreaksIf(id: $id);
        }

        return $this->resolveDependency->whatBreaksIfInContext(id: $id, context: $context);
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
            return $this->resolveDependency->showOwner(id: $id);
        }

        return $this->resolveDependency->showOwnerInContext(id: $id, context: $context);
    }

    /**
     * @param array<string, mixed> $context
     *
     * @return array<string, mixed>
     */
    public function showSlice(string $slice = null, array $context = []) : array
    {
        $slice ??= '';
        if ($context === []) {
            return $this->resolveDependency->debugSlice(slice: $slice);
        }

        return $this->resolveDependency->debugSliceInContext(slice: $slice, context: $context);
    }
}
