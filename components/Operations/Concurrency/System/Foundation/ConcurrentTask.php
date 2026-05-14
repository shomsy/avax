<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Concurrency\System\Foundation;

use Closure;
use Throwable;

final class ConcurrentTask
{
    private bool       $started  = false;
    private bool       $finished = false;
    private mixed      $result   = null;
    private Throwable|null $error = null;

    public function __construct(
        public readonly TaskId   $id,
        public readonly Closure $action,
    ) {}

    public static function create(Closure $action) : self
    {
        return new self(
            id    : TaskId::generate(),
            action: $action,
        );
    }

    public function isStarted() : bool
    {
        return $this->started;
    }

    public function isFinished() : bool
    {
        return $this->finished;
    }

    public function isRunning() : bool
    {
        return $this->started && ! $this->finished;
    }

    public function getResult() : mixed
    {
        return $this->result;
    }

    public function getError() : Throwable|null
    {
        return $this->error;
    }

    public function execute() : mixed
    {
        $this->start();

        try {
            $result = ($this->action)();
            $this->complete($result);

            return $result;
        } catch (Throwable $e) {
            $this->fail($e);
            throw $e;
        }
    }

    public function start() : void
    {
        $this->started = true;
    }

    public function complete(mixed $result) : void
    {
        $this->finished = true;
        $this->result   = $result;
    }

    public function fail(Throwable $error) : void
    {
        $this->finished = true;
        $this->error    = $error;
    }
}
