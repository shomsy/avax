<?php

declare(strict_types=1);

namespace Avax\Framework\System\Runtime\ReactPhp;

use Avax\Components\HTTP\Response\ResponseFactory;
use Avax\Framework\System\Capabilities\Runtime\RuntimeResponse;
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
 */
final class RunReactHttpServer
{
    private LoopInterface $loop;
    private ?HttpServer $server = null;

    public function __construct(
        private string $host = '127.0.0.1',
        private int $port = 8080,
    ) {
        $this->loop = Loop::get();
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
}
