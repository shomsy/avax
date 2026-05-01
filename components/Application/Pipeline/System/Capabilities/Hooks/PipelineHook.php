<?php

declare(strict_types=1);

namespace Avax\Components\Application\Pipeline\System\Capabilities\Hooks;

use Closure;

final readonly class PipelineHook
{
    public const string BEFORE_ROUTE = 'beforeRoute';

    public const string AFTER_ROUTE = 'afterRoute';

    public const string BEFORE_CONTROLLER = 'beforeController';

    public const string AFTER_CONTROLLER = 'afterController';

    public const string BEFORE_RESPONSE = 'beforeResponse';

    public const string AFTER_RESPONSE = 'afterResponse';

    public const string ON_EXCEPTION = 'onException';

    public const string ON_TERMINATE = 'onTerminate';

    public function __construct(
        public string  $name,
        public Closure $handler,
        public int     $priority = 0,
    ) {}
}

final readonly class PipelineStage
{
    public function __construct(
        public string $name,
        public bool   $stopped = false,
        public mixed  $data = null,
    ) {}

    public function stop(mixed $data = null) : self
    {
        return new self($this->name, true, $data ?? $this->data);
    }
}

final class StagePipeline
{
    /** @var list<PipelineHook> */
    private array $hooks = [];

    public function register(PipelineHook $pipelineHook) : void
    {
        $this->hooks[] = $pipelineHook;
        usort($this->hooks, fn ($a, $b) : int => $b->priority <=> $a->priority);
    }

    public function execute(string $stage, mixed $initial = null) : mixed
    {
        $result = $initial;

        foreach ($this->hooks as $hook) {
            if ($hook->name === $stage) {
                $result = $hook->handler($result);
            }
        }

        return $result;
    }

    public function hasHooks(string $stage) : bool
    {
        foreach ($this->hooks as $hook) {
            if ($hook->name === $stage) {
                return true;
            }
        }

        return false;
    }
}