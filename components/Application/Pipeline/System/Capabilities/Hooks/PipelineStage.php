<?php

declare(strict_types=1);

namespace Avax\Components\Application\Pipeline\System\Capabilities\Hooks;

final readonly class PipelineStage
{
    public function __construct(
        public string $name,
        public bool $stopped = false,
        public mixed $data = null,
    ) {
    }

    public function stop(mixed $data = null): self
    {
        return new self($this->name, true, $data ?? $this->data);
    }
}
