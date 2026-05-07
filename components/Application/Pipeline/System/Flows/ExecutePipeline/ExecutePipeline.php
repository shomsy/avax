<?php

declare(strict_types=1);

namespace Avax\Components\Application\Pipeline\System\Flows\ExecutePipeline;

final readonly class ExecutePipeline
{
    /**
     * @param list<callable> $pipes
     */
    public function execute(mixed $payload, array $pipes) : mixed
    {
        $result = $payload;
        foreach ($pipes as $pipe) {
            $result = $pipe($result);
        }

        return $result;
    }
}
