<?php

declare(strict_types=1);

namespace Avax\Components\Server\System\PublicSurface;

use Avax\Components\Server\System\Capabilities\RunApplicationOnRunApplicationOnPhpBuiltInServer;
use RuntimeException;

final readonly class Server
{
    public static function serve(
        string $host = '0.0.0.0',
        int    $port = 8000,
        string $router = 'auto',
    ) : ServerResult
    {
        $routerFile = match ($router) {
            'auto'  => self::findRouterFile(),
            default => $router,
        };

        if (! file_exists($routerFile)) {
            return ServerResult::error('Router file not found: ' . $routerFile);
        }

        $started = RunApplicationOnRunApplicationOnPhpBuiltInServer::start(
            host  : $host,
            port  : $port,
            router: $routerFile,
        );

        if ($started) {
            return ServerResult::success(
                host  : $host,
                port  : $port,
                router: $routerFile,
            );
        }

        return ServerResult::error(sprintf('Failed to start server on %s:%d', $host, $port));
    }

    public static function findRouterFile() : string
    {
        $candidates = [
            'routes/web.php',
            'routes/api.php',
            'bootstrap/routes.php',
            'public/index.php',
            'index.php',
        ];

        $basePath = dirname(path: __DIR__, levels: 4);

        foreach ($candidates as $candidate) {
            $path = $basePath . '/' . $candidate;
            if (file_exists($path)) {
                return $path;
            }
        }

        throw new RuntimeException('No router file found. Create routes/web.php or routes/api.php');
    }

    public static function findPublicPath() : string
    {
        $basePath   = dirname(path: __DIR__, levels: 4);
        $publicPath = $basePath . '/public';

        if (! is_dir($publicPath)) {
            mkdir($publicPath, 0o755, true);
        }

        return $publicPath;
    }
}

final class ServerResult
{
    private function __construct(
        public bool   $success,
        public string $message = '',
        public string $host = '0.0.0.0',
        public int    $port = 8000,
        public string $router = '',
    ) {}

    public static function success(
        string $host,
        int    $port,
        string $router,
    ) : self
    {
        return new self(
            success: true,
            message: sprintf('Server started at http://%s:%d', $host, $port),
            host   : $host,
            port   : $port,
            router : $router,
        );
    }

    public static function error(string $message) : self
    {
        return new self(
            success: false,
            message: $message,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray() : array
    {
        return [
            'success' => $this->success,
            'message' => $this->message,
            'host'    => $this->host,
            'port'    => $this->port,
            'router'  => $this->router,
            'url'     => $this->success ? sprintf('http://%s:%d', $this->host, $this->port) : null,
        ];
    }
}
