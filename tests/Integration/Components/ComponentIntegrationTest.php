<?php

declare(strict_types=1);

namespace Avax\Tests\Integration\Components;

use Avax\Components\Operations\Filesystem\System\Capabilities\StorageBackends\LocalStorageAdapter;
use Avax\Components\Operations\Mail\System\Capabilities\Queue\Mail;
use Avax\Components\Operations\Mail\System\Capabilities\Queue\Mailable;
use Avax\Components\Operations\Realtime\System\Capabilities\WebSocket\WebSocketServer;
use Avax\Components\Operations\Resilience\System\Capabilities\RateLimiter\RateLimit;
use Avax\Components\Operations\Resilience\System\Capabilities\RateLimiter\RedisRateLimiter;
use Avax\Framework\System\Capabilities\Runtime\Capabilities\RunApplicationOnPhpBuiltInServer as PhpBuiltInServer;
use Avax\Tests\TestCase;
use PHPUnit\Framework\Attributes\Group;

#[Group('serial')]
class ComponentIntegrationTest extends TestCase
{
    public function test_server_can_find_public_path(): void
    {
        $path = PhpBuiltInServer::findDocumentRoot();

        $this->assertIsString($path);
        $this->assertDirectoryExists($path);
    }

    public function test_server_can_check_port_availability(): void
    {
        $available = ! PhpBuiltInServer::isPortInUse(65432);
        $this->assertTrue($available);
    }

    public function test_rate_limit_remaining(): void
    {
        // Use array driver (in-memory) — works without ext-redis.
        $limiter = new RedisRateLimiter(config: ['driver' => 'array']);
        RateLimit::setLimiter($limiter);

        $key = 'test_rate_limit_'.uniqid();

        $remaining = $limiter->remaining($key, 10);

        $this->assertEquals(10, $remaining);
    }

    public function test_websocket_connect_disconnect(): void
    {
        $connectionId = 'test_conn_'.uniqid();

        WebSocketServer::connect($connectionId, 'test-channel');

        $connections = WebSocketServer::connections('test-channel');

        $this->assertContains($connectionId, $connections);

        WebSocketServer::disconnect($connectionId);
    }

    public function test_storage_put_and_get(): void
    {
        $tmpDir = sys_get_temp_dir() . '/avax_storage_test_' . uniqid();
        mkdir($tmpDir, 0o755, true);

        $storage = new LocalStorageAdapter(config: ['root' => $tmpDir]);

        $path = 'test/'.uniqid().'.txt';
        $content = 'Hello, Avax!';

        $this->assertTrue($storage->put($path, $content));
        $this->assertTrue($storage->exists($path));
        $this->assertEquals($content, $storage->get($path));
        $this->assertEquals(strlen($content), $storage->size($path));

        $storage->delete($path);
        $this->assertFalse($storage->exists($path));

        // Cleanup
        $this->removeDirectoryRecursive($tmpDir);
    }

    public function test_storage_url_generation(): void
    {
        $storage = new LocalStorageAdapter();

        $url = $storage->url('test/file.txt');

        $this->assertStringContainsString('test/file.txt', $url);
    }

    public function test_mailable_builder(): void
    {
        $mailable = Mail::to('test@example.com')
            ->subject('Test Subject')
            ->body('Test Body');

        $this->assertEquals('test@example.com', $mailable->getTo());
        $this->assertEquals('Test Subject', $mailable->getSubject());
        $this->assertEquals('Test Body', $mailable->getBody());
    }

    public function test_mailable_class(): void
    {
        $mailable = new Mailable();
        $mailable->to('user@example.com')
            ->subject('Welcome')
            ->body('<h1>Welcome!</h1>')
            ->from('noreply@avax.io', 'Avax');

        $this->assertEquals('user@example.com', $mailable->getTo());
        $this->assertEquals('Welcome', $mailable->getSubject());
    }

    private function removeDirectoryRecursive(string $path): void
    {
        if (!is_dir($path)) {
            return;
        }
        $items = scandir($path);
        if ($items === false) {
            return;
        }
        foreach ($items as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }
            $itemPath = $path . '/' . $item;
            if (is_dir($itemPath)) {
                $this->removeDirectoryRecursive($itemPath);
            } else {
                unlink($itemPath);
            }
        }
        rmdir($path);
    }
}
