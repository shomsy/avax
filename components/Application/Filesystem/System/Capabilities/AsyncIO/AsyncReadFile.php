<?php

declare(strict_types=1);

namespace Avax\Components\Application\Filesystem\System\Capabilities\AsyncIO;

use LogicException;

/**
 * Value object representing an async file read operation.
 *
 * This is a descriptor/DTO for a read operation — NOT a sync implementation.
 * It encapsulates the path, options, and associated promise so that the
 * operation can be passed around, queued, batched, or inspected before
 * or during execution.
 *
 * Usage:
 *   $readOp = new AsyncReadFile('/path/to/file', ['encoding' => 'utf-8'], $promise);
 *   $result = $readOp->promise()->then(fn (string $content) => trim($content));
 */
final readonly class AsyncReadFile
{
    /**
     * @param string                     $path    Absolute or relative file path to read
     * @param array<string, mixed>       $options Read operation options (encoding, offset, length, etc.)
     * @param AsyncOperationPromise|null $promise The promise associated with this operation (null until scheduled)
     */
    public function __construct(
        public string                 $path,
        public array                  $options = [],
        public ?AsyncOperationPromise $promise = null,
    ) {}

    /**
     * Create a read operation descriptor without an associated promise.
     *
     * The promise will be attached when the operation is scheduled
     * by an AsyncFilesystemInterface implementation.
     */
    public static function create(string $path, array $options = []) : self
    {
        return new self($path, $options);
    }

    /**
     * Create a read operation descriptor with an attached promise.
     *
     * Use this when the operation has already been scheduled and
     * you have a promise from the async filesystem implementation.
     */
    public static function withPromise(
        string                $path,
        AsyncOperationPromise $promise,
        array                 $options = [],
    ) : self
    {
        return new self($path, $options, $promise);
    }

    /**
     * Check if this operation has been scheduled (has an attached promise).
     */
    public function isScheduled() : bool
    {
        return $this->promise !== null;
    }

    /**
     * Attach a promise to this operation (called by the scheduler).
     *
     * @throws LogicException If a promise is already attached
     */
    public function attachPromise(AsyncOperationPromise $promise) : self
    {
        if ($this->promise !== null) {
            throw new LogicException(
                sprintf('Cannot attach promise to read operation for "%s": promise already set', $this->path)
            );
        }

        // Cannot modify readonly class, so we return a new instance
        return new self($this->path, $this->options, $promise);
    }
}
