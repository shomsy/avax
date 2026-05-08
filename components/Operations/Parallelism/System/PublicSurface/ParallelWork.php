<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Parallelism\System\PublicSurface;

use Closure;

final readonly class ParallelWork
{
    /**
     * @param string|int       $name
     * @param Closure(): mixed $action
     */
    public function __construct(
        public string|int $name,
        public Closure    $action,
    ) {}

    public function getName() : string|int
    {
        return $this->name;
    }

    public function execute() : mixed
    {
        return ($this->action)();
    }
}
