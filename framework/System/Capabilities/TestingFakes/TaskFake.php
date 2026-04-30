<?php

declare(strict_types=1);

namespace Avax\Framework\System\Capabilities\TestingFakes;

final class TaskFake
{
    /** @var list<object> */
    private array $dispatched = [];

    public function dispatch(object $task) : void
    {
        $this->dispatched[] = $task;
    }

    public function assertDispatched(string $taskClass) : self
    {
        foreach ($this->dispatched as $task) {
            if ($task instanceof $taskClass) {
                return $this;
            }
        }

        throw new TestingFakeException(sprintf("Task '%s' was not dispatched", $taskClass));
    }

    /**
     * @return list<object>
     */
    public function dispatched() : array
    {
        return $this->dispatched;
    }

    public function clear() : void
    {
        $this->dispatched = [];
    }
}
