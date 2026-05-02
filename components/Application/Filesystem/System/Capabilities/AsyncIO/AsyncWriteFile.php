<?php

declare(strict_types=1);

namespace Avax\Components\Application\Filesystem\System\Capabilities\AsyncIO;

use LogicException;

/**
 * Value object representing an async file write operation.
 *
 * This is a descriptor/DTO for a write operation — NOT a sync implementation.
 * It encapsulates the path, contents, options, and associated promise so that
 * the operation can be passed around, queued, batched, or inspected before
 * or during execution.
 *
 * Usage:
 *   $writeOp = new AsyncWriteFile('/path/to/file', 'hello world', ['permissions' => 0644], $promise);
 *   $writeOp->promise()->then(fn (bool $success) => $success ? 'written' : 'failed');
 */
final readonly class AsyncWriteFile
{
    /**
     * @param string $path Absolute or relative file path to write
     * @param string $contents File contents to write
     * @param array<string, mixed> $options Write operation options (permissions, append mode, etc.)
     * @param AsyncOperationPromise|null $asyncOperationPromise The promise associated with this operation (null until scheduled)
     */
    public function __construct(
        public string $path,
        public string $contents,
        public array $options = [],
        public ?AsyncOperationPromise $asyncOperationPromise = null,
    ) {
    }

    /**
     * Create a write operation descriptor without an associated promise.
     *
     * The promise will be attached when the operation is scheduled
     * by an AsyncFilesystemInterface implementation.
     */
    public static function create(string $path, string $contents, array $options = []): self
    {
        return new self($path, $contents, $options);
    }

    /**
     * Create a write operation descriptor with an attached promise.
     *
     * Use this when the operation has already been scheduled and
     * you have a promise from the async filesystem implementation.
     */
    public static function withPromise(
        string $path,
        string $contents,
        AsyncOperationPromise $asyncOperationPromise,
        array $options = [],
    ): self {
        return new self($path, $contents, $options, $asyncOperationPromise);
    }

    /**
     * Check if this operation has been scheduled (has an attached promise).
     */
    public function isScheduled(): bool
    {
        return $this->asyncOperationPromise instanceof AsyncOperationPromise;
    }

    /**
     * Attach a promise to this operation (called by the scheduler).
     *
     * @throws LogicException If a promise is already attached
     */
    public function attachPromise(AsyncOperationPromise $asyncOperationPromise): self
    {
        if ($this->asyncOperationPromise instanceof AsyncOperationPromise) {
            throw new LogicException(
                sprintf('Cannot attach promise to write operation for "%s": promise already set', $this->path),
            );
        }

        // Cannot modify readonly class, so we return a new instance
        return new self($this->path, $this->contents, $this->options, $asyncOperationPromise);
    }
}
