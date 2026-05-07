<?php

declare(strict_types=1);

namespace Avax\Components\Application\Pipeline\System\Capabilities\Hooks;

final class StagePipeline
{
    /** @var list<PipelineHook> */
    private array $hooks = [];

    public function register(PipelineHook $pipelineHook): void
    {
        $this->hooks[] = $pipelineHook;
        usort($this->hooks, static fn ($a, $b): int => $b->priority <=> $a->priority);
    }

    public function execute(string $stage, mixed $initial = null): mixed
    {
        $result = $initial;

        foreach ($this->hooks as $hook) {
            if ($hook->name === $stage) {
                $handler = $hook->handler;
                $result  = $handler($result);

                if ($result instanceof PipelineStage && $result->stopped) {
                    return $result->data;
                }
            }
        }

        return $result;
    }

    public function hasHooks(string $stage): bool
    {
        return array_any($this->hooks, fn ($hook): bool => $hook->name === $stage);
    }
}
