<?php

declare(strict_types=1);

namespace Avax\Components\Application\Filesystem\System\Capabilities\AsyncIO;

use Override;
use Throwable;

/**
 * Internal promise implementation for the RunFilesystemIoSynchronously.
 *
 * This is a private, immediately-resolved/rejected promise. It exists
 * ONLY to fulfill the AsyncOperationPromise contract for the sync adapter.
 *
 * DO NOT use this class outside of RunFilesystemIoSynchronously. When a real
 * async runtime adapter is implemented, it should provide its own promise
 * class that integrates with the event loop or fiber scheduler.
 *
 * @internal
 */
final class SyncOperationPromise implements AsyncOperationPromise
{
    private bool $isResolved;

    private bool $isRejected;

    private mixed $result = null;

    private ?Throwable $throwable = null;

    /**
     * @param mixed $result The resolved value (if resolved)
     * @param Throwable|null $throwable The rejection exception (if rejected)
     */
    public function __construct(
        mixed     $result = null,
        ?Throwable $throwable = null,
    )
    {
        if ($throwable instanceof Throwable) {
            $this->isResolved = false;
            $this->isRejected = true;
            $this->throwable = $throwable;
        } else {
            $this->isResolved = true;
            $this->isRejected = false;
            $this->result = $result;
        }
    }

    #[Override]
    public function then(callable $onResolved) : AsyncOperationPromise
    {
        if ($this->isResolved) {
            try {
                $newResult = $onResolved($this->result);

                return new self(result: $newResult);
            } catch (Throwable $e) {
                return new self(rejection: $e);
            }
        }

        // If rejected, pass through without calling onResolved
        return $this;
    }

    #[Override]
    public function catch(callable $onRejected) : AsyncOperationPromise
    {
        if ($this->isRejected && $this->throwable instanceof Throwable) {
            try {
                $newResult = $onRejected($this->throwable);

                return new self(result: $newResult);
            } catch (Throwable $e) {
                return new self(rejection: $e);
            }
        }

        // If resolved, pass through without calling onRejected
        return $this;
    }

    #[Override]
    public function isResolved() : bool
    {
        return $this->isResolved;
    }

    #[Override]
    public function isRejected() : bool
    {
        return $this->isRejected;
    }

    #[Override]
    public function getResult() : mixed
    {
        if ($this->isRejected && $this->throwable instanceof Throwable) {
            throw $this->throwable;
        }

        return $this->result;
    }
}
