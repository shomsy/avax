<?php

declare(strict_types=1);

namespace Avax\Components\Application\Pipeline\System\Flows\BuildPipeline;

final readonly class BuildPipeline
{
    /**
     * @param list<callable> $pipes
     * @param list<callable> $before
     * @param list<callable> $after
     *
     * @return list<callable>
     */
    public function build(array $pipes, array $before = [], array $after = []) : array
    {
        return [...$before, ...$pipes, ...$after];
    }
}
