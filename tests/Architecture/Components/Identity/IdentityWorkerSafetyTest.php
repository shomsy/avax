<?php

declare(strict_types=1);

namespace Avax\Tests\Architecture\Components\Identity;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * Verifies worker safety for Identity components:
 * - All InMemory stores have public reset() methods
 * - Auth singleton has resetInstance()
 * - Static facade classes have reset() methods
 * - Reset actually clears state (behavioral spot checks)
 */
final class IdentityWorkerSafetyTest extends TestCase
{
    private string $identityPath;

    protected function setUp(): void
    {
        $this->identityPath = dirname(__DIR__, 4) . '/components/Identity';
    }

    protected function tearDown(): void
    {
        \Avax\Components\Identity\Auth\System\PublicSurface\Auth::resetInstance();
    }

    /**
     * @return list<string> List of file paths containing InMemory classes
     */
    private function collectInMemoryFiles(): array
    {
        $files = [];
        $iterator = new \RecursiveDirectoryIterator($this->identityPath);
        $recursive = new \RecursiveIteratorIterator($iterator, \RecursiveIteratorIterator::LEAVES_ONLY);

        foreach ($recursive as $file) {
            if (!$file->isFile() || $file->getExtension() !== 'php') {
                continue;
            }

            if (str_contains($file->getFilename(), 'InMemory')) {
                $files[] = $file->getPathname();
            }
        }

        return $files;
    }

    #[Test]
    public function all_in_memory_stores_have_reset_method(): void
    {
        $files = $this->collectInMemoryFiles();
        $missing = [];

        foreach ($files as $filepath) {
            $content = file_get_contents($filepath);
            if ($content === false) {
                continue;
            }

            // Check for public function reset()
            $hasPublicReset = preg_match('/public\s+function\s+reset\s*\(/m', $content) === 1;

            if (!$hasPublicReset) {
                $missing[] = $filepath;
            }
        }

        $this->assertEmpty(
            $missing,
            'All InMemory stores must have a public reset() method for worker safety. Missing: ' . implode(', ', $missing),
        );
    }

    #[Test]
    public function auth_has_reset_instance_method(): void
    {
        $authFile = $this->identityPath . '/Auth/System/PublicSurface/Auth.php';
        $content = file_get_contents($authFile);

        $this->assertNotFalse($content);

        $this->assertMatchesRegularExpression(
            '/public\s+static\s+function\s+resetInstance\s*\(/m',
            $content,
            'Auth must have a public static resetInstance() method for long-lived worker safety.',
        );
    }

    #[Test]
    public function static_facade_reset_methods_exist(): void
    {
        $facades = [
            'Tokens/System/Capabilities/JwtAuth/JwtAuth.php' => 'reset',
            'Access/System/Capabilities/Policy/Policy.php' => 'reset',
            'Tenancy/System/Capabilities/Context/TenantContext.php' => 'reset',
            'ExternalIdentity/System/PublicSurface/ExternalIdentity.php' => 'reset',
        ];

        $missing = [];

        foreach ($facades as $relativePath => $method) {
            $filepath = $this->identityPath . '/' . $relativePath;
            $content = file_get_contents($filepath);

            if ($content === false) {
                $missing[] = $relativePath . ' (file not found)';
                continue;
            }

            $pattern = '/public\s+static\s+function\s+' . preg_quote($method, '/') . '\s*\(/m';

            if (preg_match($pattern, $content) !== 1) {
                $missing[] = $relativePath . '::' . $method . '()';
            }
        }

        $this->assertEmpty(
            $missing,
            'Static facade classes must have public static reset() methods. Missing: ' . implode(', ', $missing),
        );
    }

    #[Test]
    public function in_memory_user_source_reset_clears_state(): void
    {
        $store = new \Avax\Components\Identity\Auth\System\Capabilities\Identity\UserSource\InMemoryUserSource();

        // Add some state
        $userId = new \Avax\Components\Identity\Auth\System\Capabilities\Identity\User\UserId(1);
        $email = new \Avax\Components\Identity\Auth\System\Capabilities\Identity\User\UserEmail('test@example.com');
        $user = \Avax\Components\Identity\Auth\System\Capabilities\Identity\User\User::create(
            id: $userId,
            email: $email,
            username: 'testuser',
            passwordHash: 'hashed',
            isActive: true,
        );
        $store->create($user);

        // Verify state exists
        $this->assertNotNull($store->findById($userId));

        // Reset
        $store->reset();

        // Verify state cleared
        $this->assertNull($store->findById($userId));
    }

    #[Test]
    public function in_memory_session_registry_reset_clears_state(): void
    {
        $registry = new \Avax\Components\Identity\Auth\System\Capabilities\Identity\Sessions\Registry\InMemorySessionRegistry();

        $sessionId = 'test-session-1';
        $userId = new \Avax\Components\Identity\Auth\System\Capabilities\Identity\User\UserId(1);
        $now = new \DateTimeImmutable();

        $record = new \Avax\Components\Identity\Auth\System\Capabilities\Identity\Sessions\Registry\SessionRecord(
            sessionId: $sessionId,
            userId: $userId,
            createdAt: $now,
            lastSeenAt: $now,
            idleExpiresAt: $now->modify('+1 hour'),
            absoluteExpiresAt: $now->modify('+24 hours'),
        );
        $registry->save($record);

        $this->assertNotNull($registry->find($sessionId));

        $registry->reset();

        $this->assertNull($registry->find($sessionId));
    }

    #[Test]
    public function in_memory_credential_store_reset_clears_state(): void
    {
        $store = new \Avax\Components\Identity\Credentials\System\Capabilities\CredentialStore\InMemoryCredentialStore();

        $userId = '1';
        $store->store($userId, ['hash' => 'password_hash_test']);

        $this->assertNotNull($store->read($userId));

        $store->reset();

        $this->assertNull($store->read($userId));
    }

    #[Test]
    public function auth_reset_instance_clears_static_state(): void
    {
        // Create a minimal mock — we can't construct a real Auth without full Identity graph
        // Instead, test that resetInstance actually nullifies the static reference
        $auth = \Avax\Components\Identity\Auth\System\PublicSurface\Auth::class;

        // After resetInstance, instance() should throw
        $auth::resetInstance();

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Auth instance not set');
        $auth::instance();
    }
}
