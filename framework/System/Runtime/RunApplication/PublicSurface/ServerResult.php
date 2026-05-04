<?php

declare(strict_types=1);

namespace Avax\Framework\System\Runtime\RunApplication\PublicSurface;

use Avax\Framework\System\Runtime\RunApplication\Capabilities\RunApplicationOnRunApplicationOnPhpBuiltInServer;

final class ServerResult
{
    private function __construct(
        public bool   $success,
        public string $message = '',
        public string $host = '0.0.0.0',
        public int    $port = 8000,
        public string $router = '',
    )
    {
    }

    public static function success(
        string $host,
        int    $port,
        string $router,
    ): self
    {
        return new self(
            success: true,
            message: sprintf('Server started at http://%s:%d', $host, $port),
            host: $host,
            port: $port,
            router: $router,
        );
    }

    public static function error(string $message): self
    {
        return new self(
            success: false,
            message: $message,
        );
    }

    public function toArray(): array
    {
        return [
            'success' => $this->success,
            'message' => $this->message,
            'host' => $this->host,
            'port' => $this->port,
            'router' => $this->router,
            'url' => $this->success ? sprintf('http://%s:%d', $this->host, $this->port) : null,
        ];
    }
}
