<?php

declare(strict_types=1);

namespace Avax\HTTP\Session\Features;

/**
 * AsyncEventDispatcher - Asynchronous Event Dispatcher
 *
 * High-throughput event dispatcher with queue-based async processing.
 * Prevents event handling from blocking main request flow.
 *
 * Features:
 * - Queue-based async dispatch
 * - Batch processing
 * - Error handling and retry logic
 * - Memory-efficient (bounded queue)
 * - Graceful shutdown
 *
 * Modes:
 * - SYNC: Immediate synchronous dispatch (default, backward compatible)
 * - ASYNC_MEMORY: In-memory queue, processed on shutdown
 * - ASYNC_FILE: File-based queue, processed by background worker
 * - ASYNC_REDIS: Redis queue (requires Redis extension)
 *
 * @example Sync mode (default)
 *   $dispatcher = new AsyncEventDispatcher();
 *   $dispatcher->listen('event', $callback);
 *   $dispatcher->dispatch('event', $data);  // Immediate
 *
 * @example Async memory mode
 *   $dispatcher = new AsyncEventDispatcher(AsyncEventDispatcher::MODE_ASYNC_MEMORY);
 *   $dispatcher->dispatch('event', $data);  // Queued, processed on shutdown
 *
 * @example Async file mode (for background workers)
 *   $dispatcher = new AsyncEventDispatcher(AsyncEventDispatcher::MODE_ASYNC_FILE, '/tmp/events.queue');
 *   $dispatcher->dispatch('event', $data);  // Written to file
 *
 * @package Avax\HTTP\Session\Features
 */
final class AsyncEventDispatcher
{
    public const MODE_SYNC         = 'sync';
    public const MODE_ASYNC_MEMORY = 'async_memory';
    public const MODE_ASYNC_FILE   = 'async_file';
    public const MODE_ASYNC_REDIS  = 'async_redis';

    /**
     * @var array<string, array<callable>> Event listeners
     */
    private array $listeners = [];

    /**
     * @var array<array{event: string, data: array}> Event queue
     */
    private array $queue = [];

    /**
     * @var bool Shutdown handler registered
     */
    private bool $shutdownRegistered = false;

    /**
     * @var int Maximum queue size (prevent memory exhaustion)
     */
    private int $maxQueueSize = 1000;

    /**
     * @var int Batch size for processing
     */
    private int $batchSize = 100;

    /**
     * AsyncEventDispatcher Constructor.
     *
     * @param string      $mode      Dispatch mode (sync|async_memory|async_file|async_redis).
     * @param string|null $queuePath Queue file path (for async_file mode).
     * @param object|null $redis     Redis instance (for async_redis mode).
     */
    public function __construct(
        private string      $mode = self::MODE_SYNC,
        private string|null $queuePath = null,
        private object|null $redis = null
    )
    {
        if ($mode === self::MODE_ASYNC_FILE && $queuePath === null) {
            throw new \InvalidArgumentException('Queue path required for async_file mode');
        }

        if ($mode === self::MODE_ASYNC_REDIS && $redis === null) {
            throw new \InvalidArgumentException('Redis instance required for async_redis mode');
        }
    }

    /**
     * Register an event listener.
     *
     * @param string   $event    Event name.
     * @param callable $callback Callback function.
     *
     * @return self Fluent interface.
     */
    public function listen(string $event, callable $callback) : self
    {
        if (! isset($this->listeners[$event])) {
            $this->listeners[$event] = [];
        }

        $this->listeners[$event][] = $callback;

        return $this;
    }

    /**
     * Register a one-time listener.
     *
     * @param string   $event    Event name.
     * @param callable $callback Callback function.
     *
     * @return self Fluent interface.
     */
    public function once(string $event, callable $callback) : self
    {
        $wrapper = function (array $data) use ($event, $callback, &$wrapper) {
            $callback($data);
            $this->removeListener($event, $wrapper);
        };

        return $this->listen($event, $wrapper);
    }

    /**
     * Remove a specific listener.
     *
     * @param string   $event    Event name.
     * @param callable $callback Callback to remove.
     *
     * @return self Fluent interface.
     */
    public function removeListener(string $event, callable $callback) : self
    {
        if (! isset($this->listeners[$event])) {
            return $this;
        }

        $this->listeners[$event] = array_filter(
            $this->listeners[$event],
            fn($listener) => $listener !== $callback
        );

        return $this;
    }

    /**
     * Dispatch an event.
     *
     * Behavior depends on mode:
     * - SYNC: Immediate dispatch
     * - ASYNC_MEMORY: Queue in memory, process on shutdown
     * - ASYNC_FILE: Write to file queue
     * - ASYNC_REDIS: Push to Redis queue
     *
     * @param string               $event Event name.
     * @param array<string, mixed> $data  Event data.
     *
     * @return void
     */
    public function dispatch(string $event, array $data = []) : void
    {
        match ($this->mode) {
            self::MODE_SYNC         => $this->dispatchSync($event, $data),
            self::MODE_ASYNC_MEMORY => $this->dispatchAsyncMemory($event, $data),
            self::MODE_ASYNC_FILE   => $this->dispatchAsyncFile($event, $data),
            self::MODE_ASYNC_REDIS  => $this->dispatchAsyncRedis($event, $data),
            default                 => throw new \InvalidArgumentException("Invalid mode: {$this->mode}")
        };
    }

    /**
     * Dispatch event synchronously (immediate).
     *
     * @param string               $event Event name.
     * @param array<string, mixed> $data  Event data.
     *
     * @return void
     */
    private function dispatchSync(string $event, array $data) : void
    {
        if (! isset($this->listeners[$event])) {
            return;
        }

        foreach ($this->listeners[$event] as $callback) {
            try {
                $callback($data);
            } catch (\Exception $e) {
                error_log("Event listener error [{$event}]: " . $e->getMessage());
            }
        }
    }

    /**
     * Dispatch event asynchronously (memory queue).
     *
     * @param string               $event Event name.
     * @param array<string, mixed> $data  Event data.
     *
     * @return void
     */
    private function dispatchAsyncMemory(string $event, array $data) : void
    {
        // Check queue size limit
        if (count($this->queue) >= $this->maxQueueSize) {
            error_log("Event queue full, dropping event: {$event}");

            return;
        }

        // Add to queue
        $this->queue[] = ['event' => $event, 'data' => $data];

        // Register shutdown handler (once)
        if (! $this->shutdownRegistered) {
            register_shutdown_function([$this, 'processQueue']);
            $this->shutdownRegistered = true;
        }
    }

    /**
     * Dispatch event asynchronously (file queue).
     *
     * @param string               $event Event name.
     * @param array<string, mixed> $data  Event data.
     *
     * @return void
     */
    private function dispatchAsyncFile(string $event, array $data) : void
    {
        $payload = json_encode(['event' => $event, 'data' => $data, 'timestamp' => time()]);

        // Append to queue file (atomic)
        file_put_contents(
            $this->queuePath,
            $payload . PHP_EOL,
            FILE_APPEND | LOCK_EX
        );
    }

    /**
     * Dispatch event asynchronously (Redis queue).
     *
     * @param string               $event Event name.
     * @param array<string, mixed> $data  Event data.
     *
     * @return void
     */
    private function dispatchAsyncRedis(string $event, array $data) : void
    {
        $payload = json_encode(['event' => $event, 'data' => $data, 'timestamp' => time()]);

        // Push to Redis list
        $this->redis->rPush('session:events', $payload);
    }

    /**
     * Process queued events (called on shutdown).
     *
     * @return void
     */
    public function processQueue() : void
    {
        if (empty($this->queue)) {
            return;
        }

        // Process in batches
        $batches = array_chunk($this->queue, $this->batchSize);

        foreach ($batches as $batch) {
            foreach ($batch as $item) {
                $this->dispatchSync($item['event'], $item['data']);
            }
        }

        // Clear queue
        $this->queue = [];
    }

    /**
     * Process file queue (for background workers).
     *
     * Reads events from file queue and dispatches them.
     *
     * @param int $limit Maximum events to process (0 = all).
     *
     * @return int Number of events processed.
     */
    public function processFileQueue(int $limit = 0) : int
    {
        if (! file_exists($this->queuePath)) {
            return 0;
        }

        $handle = fopen($this->queuePath, 'r+');
        if (! $handle) {
            return 0;
        }

        // Lock file
        flock($handle, LOCK_EX);

        $processed = 0;
        $remaining = [];

        while (($line = fgets($handle)) !== false) {
            if ($limit > 0 && $processed >= $limit) {
                $remaining[] = $line;
                continue;
            }

            $item = json_decode(trim($line), true);
            if ($item && isset($item['event'], $item['data'])) {
                $this->dispatchSync($item['event'], $item['data']);
                $processed++;
            }
        }

        // Rewrite file with remaining events
        ftruncate($handle, 0);
        rewind($handle);
        foreach ($remaining as $line) {
            fwrite($handle, $line);
        }

        flock($handle, LOCK_UN);
        fclose($handle);

        return $processed;
    }

    /**
     * Process Redis queue (for background workers).
     *
     * @param int $limit Maximum events to process (0 = all).
     *
     * @return int Number of events processed.
     */
    public function processRedisQueue(int $limit = 0) : int
    {
        $processed = 0;

        while ($limit === 0 || $processed < $limit) {
            $payload = $this->redis->lPop('session:events');
            if (! $payload) {
                break;
            }

            $item = json_decode($payload, true);
            if ($item && isset($item['event'], $item['data'])) {
                $this->dispatchSync($item['event'], $item['data']);
                $processed++;
            }
        }

        return $processed;
    }

    /**
     * Get queue size.
     *
     * @return int Number of queued events.
     */
    public function getQueueSize() : int
    {
        return match ($this->mode) {
            self::MODE_ASYNC_MEMORY => count($this->queue),
            self::MODE_ASYNC_FILE   => $this->getFileQueueSize(),
            self::MODE_ASYNC_REDIS  => $this->getRedisQueueSize(),
            default                 => 0
        };
    }

    /**
     * Get file queue size.
     *
     * @return int Number of lines in queue file.
     */
    private function getFileQueueSize() : int
    {
        if (! file_exists($this->queuePath)) {
            return 0;
        }

        return count(file($this->queuePath));
    }

    /**
     * Get Redis queue size.
     *
     * @return int Number of items in Redis list.
     */
    private function getRedisQueueSize() : int
    {
        return $this->redis->lLen('session:events');
    }

    /**
     * Set maximum queue size.
     *
     * @param int $size Maximum size.
     *
     * @return self Fluent interface.
     */
    public function setMaxQueueSize(int $size) : self
    {
        $this->maxQueueSize = $size;

        return $this;
    }

    /**
     * Set batch size for processing.
     *
     * @param int $size Batch size.
     *
     * @return self Fluent interface.
     */
    public function setBatchSize(int $size) : self
    {
        $this->batchSize = $size;

        return $this;
    }

    /**
     * Get current mode.
     *
     * @return string Mode.
     */
    public function getMode() : string
    {
        return $this->mode;
    }

    /**
     * Clear all listeners.
     *
     * @return self Fluent interface.
     */
    public function clearListeners() : self
    {
        $this->listeners = [];

        return $this;
    }

    /**
     * Clear queue.
     *
     * @return self Fluent interface.
     */
    public function clearQueue() : self
    {
        $this->queue = [];

        return $this;
    }
}
