<?php

declare(strict_types=1);

namespace Avax\Framework\System\Capabilities\Runtime\ReactPhp;

use Avax\Framework\System\Capabilities\Runtime\MemoryGuard\MonitorWorkerMemory;
use Avax\Framework\System\Capabilities\Runtime\RuntimeResponse;
use Avax\Framework\System\Capabilities\Runtime\WarmApplication\HandleWarmRequest;
use Avax\Framework\System\Capabilities\Runtime\WarmApplication\ResetWarmRequestState;
use GuzzleHttp\Psr7\Response as GuzzleResponse;
use GuzzleHttp\Psr7\ServerRequest as GuzzleServerRequest;
use Psr\Http\Message\ResponseInterface as PsrResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use React\EventLoop\Loop;
use React\EventLoop\LoopInterface;
use React\Http\HttpServer;
use React\Socket\SocketServer;
use Throwable;

/**
 * RunReactHttpServer — ReactPHP-backed HTTP server for AvaX V4.
 *
 * ReactPHP-specific classes stay behind Runtime/ReactPhp.
 * No ReactPHP type appears in App public API.
 *
 * Supports warm worker safety through integration with HandleWarmRequest lifecycle.
 */
final class RunReactHttpServer
{
    private LoopInterface $loop;
    private HttpServer|null $server = null;

    private HandleWarmRequest|null   $warmHandler = null;
    private MonitorWorkerMemory|null $memoryGuard = null;

    public function __construct(
        private string $host = '127.0.0.1',
        private int $port = 8080,
    ) {
        $this->loop = Loop::get();
    }

    /**
     * Attach a warm request handler for lifecycle integration.
     */
    public function setWarmHandler(HandleWarmRequest $handler): self
    {
        $this->warmHandler = $handler;

        return $this;
    }

    /**
     * Attach a memory guard for memory monitoring.
     */
    public function setMemoryGuard(MonitorWorkerMemory $guard): self
    {
        $this->memoryGuard = $guard;

        return $this;
    }

    /**
     * Start the ReactPHP HTTP server.
     *
     * @param callable(ServerRequestInterface): PsrResponseInterface $handler
     */
    public function start(callable $handler): void
    {
        $this->server = new HttpServer($this->loop, $handler);

        $uri = "{$this->host}:{$this->port}";
        $socket = new SocketServer($uri, [], $this->loop);

        $this->server->listen($socket);

        echo "ReactPHP server listening on http://{$uri}\n";

        $this->loop->run();
    }

    /**
     * Start in smoke mode — handles exactly one request then stops.
     *
     * @param callable(ServerRequestInterface): PsrResponseInterface $handler
     */
    public function startSmoke(callable $handler): GuzzleResponse
    {
        $testRequest = new GuzzleServerRequest('GET', 'http://localhost/');
        $psrResponse = $handler($testRequest);

        return new GuzzleResponse(
            $psrResponse->getStatusCode(),
            $psrResponse->getHeaders(),
            (string) $psrResponse->getBody(),
        );
    }

    /**
     * Start in warm smoke mode — exercises the full warm request lifecycle:
     * 1. Memory capture before
     * 2. Handler execution
     * 3. Reset lifecycle runs (even on exception)
     * 4. Memory capture after
     *
     * @param callable(ServerRequestInterface): PsrResponseInterface $handler
     */
    public function startWarmSmoke(callable $handler): GuzzleResponse
    {
        $testRequest = new GuzzleServerRequest('GET', 'http://localhost/');

        // Capture memory before
        if ($this->memoryGuard !== null) {
            $this->memoryGuard->captureBefore();
        }

        $psrResponse = null;
        $thrown = null;

        try {
            $psrResponse = $handler($testRequest);
        } catch (Throwable $thrown) {
            // Captured so reset still runs
        }

        // Always run warm reset lifecycle
        if ($this->warmHandler !== null) {
            $this->warmHandler->resetter()->reset();
        }

        // Capture memory after
        if ($this->memoryGuard !== null) {
            $this->memoryGuard->captureAfter();
        }

        // Re-throw if handler failed
        if ($thrown !== null) {
            throw $thrown;
        }

        // @phpstan-ignore-next-line $psrResponse is always set via try/catch or exception re-thrown
        return new GuzzleResponse(
            $psrResponse->getStatusCode(),
            $psrResponse->getHeaders(),
            (string) $psrResponse->getBody(),
        );
    }

    /**
     * Graceful shutdown.
     */
    public function stop(): void
    {
        $this->loop->stop();
    }

    public function loop(): LoopInterface
    {
        return $this->loop;
    }

    public function warmHandler() : HandleWarmRequest|null
    {
        return $this->warmHandler;
    }

    public function memoryGuard() : MonitorWorkerMemory|null
    {
        return $this->memoryGuard;
    }
}
