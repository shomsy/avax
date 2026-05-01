<?php

declare(strict_types=1);

namespace Avax\Components\Server\System\Capabilities;

use RuntimeException;

final class PhpBuiltInServer
{
    public static function start(
        string $host,
        int $port,
        string $router,
    ): bool {
        $command = sprintf(
            'php -S %s:%d -t %s %s 2>&1',
            $host,
            $port,
            escapeshellarg(self::findDocumentRoot()),
            escapeshellarg($router),
        );

        if (self::isBackground()) {
            self::startBackground($command, $port);

            return true;
        }

        echo "🚀 Starting development server...\n";
        echo "   URL: http://{$host}:{$port}\n";
        echo "   Router: {$router}\n";
        echo "   Press Ctrl+C to stop\n\n";

        passthru($command);

        return true;
    }

    public static function findDocumentRoot(): string
    {
        $basePath   = dirname(path: __DIR__, levels: 4);
        $publicPath = $basePath . '/public';

        if (! is_dir($publicPath)) {
            mkdir($publicPath, 0o755, true);
            self::createBasicIndex($publicPath);
        }

        return $publicPath;
    }

    private static function createBasicIndex(string $publicPath): void
    {
        $index = $publicPath . '/index.php';

        if (! file_exists($index)) {
            $content = '<?php
require __DIR__ . "/../vendor/autoload.php";

echo "<h1>🚀 Avax Server</h1>";
echo "<p>Welcome to Avax Framework</p>";
echo "<p>Create routes/web.php to get started.</p>";
';
            file_put_contents($index, $content);
        }
    }

    private static function isBackground(): bool
    {
        global $argv;

        return in_array('--daemon', $argv, true) || in_array('-d', $argv, true);
    }

    private static function startBackground(string $command, int $port): void
    {
        if (self::isPortInUse($port)) {
            throw new RuntimeException("Port {$port} is already in use");
        }

        $logFile = sys_get_temp_dir() . '/avax-server-' . $port . '.log';

        $pipes = [];
        $process = proc_open($command, $descriptorSpec = [], $pipes);

        if (! is_resource($process)) {
            throw new RuntimeException('Failed to start server');
        }

        proc_close($process);
    }

    public static function isPortInUse(int $port): bool
    {
        $connection = @fsockopen('127.0.0.1', $port, $errno, $errstr, 1);

        if ($connection) {
            fclose($connection);

            return true;
        }

        return false;
    }

    public static function startWithOptions(array $options): bool
    {
        $host = $options['host'] ?? '0.0.0.0';
        $port = $options['port'] ?? 8000;
        $router = $options['router'] ?? 'auto';
        $public = $options['public'] ?? self::findDocumentRoot();

        $routerFile = $router === 'auto' ? self::findRouterFile() : $router;

        $command = sprintf(
            'php -S %s:%d -t %s %s',
            $host,
            $port,
            escapeshellarg($public),
            escapeshellarg($routerFile),
        );

        echo "🚀 Server starting at http://{$host}:{$port}\n";
        passthru($command);

        return true;
    }

    public static function findRouterFile(): string
    {
        $basePath = dirname(path: __DIR__, levels: 4);

        $candidates = [
            'routes/web.php',
            'routes/api.php',
            'bootstrap/routes.php',
            'public/index.php',
            $basePath . '/router.php',
        ];

        foreach ($candidates as $candidate) {
            if (file_exists($candidate)) {
                return $candidate;
            }
        }

        return $basePath . '/router.php';
    }

    public static function stop(int $port): bool
    {
        if (PHP_OS_FAMILY === 'Windows') {
            exec('taskkill /F /IM php.exe /FI "WINDOWTITLE like%AvaX%"');
        } else {
            exec("pkill -f 'php -S {$port}'");
        }

        echo "✅ Server stopped on port {$port}\n";

        return true;
    }
}
