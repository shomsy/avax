<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\HTTP\Session;

use Avax\Components\HTTP\Session\System\Capabilities\Storage\ArraySessionStore;
use Avax\Components\HTTP\Session\System\PublicSurface\SessionScope;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

/**
 * Unit tests for the Session functionality using ArraySessionStore.
 */
final class SessionTest extends TestCase
{
    private ArraySessionStore $store;
    private SessionScope $scope;

    #[Test]
    public function session_is_not_started_initially() : void
    {
        $this->assertFalse($this->scope->isStarted());
    }

    #[Test]
    public function session_can_set_and_get_values() : void
    {
        // Simulate started session by directly manipulating data
        $reflection = new ReflectionClass($this->scope);

        $startedProp = $reflection->getProperty('started');
        $startedProp->setValue($this->scope, true);

        $idProp = $reflection->getProperty('id');
        $idProp->setValue($this->scope, 'test-session-id');

        $this->scope->set('user_name', 'John');
        $this->assertEquals('John', $this->scope->get('user_name'));
    }

    #[Test]
    public function session_returns_default_for_missing_key() : void
    {
        $reflection  = new ReflectionClass($this->scope);
        $startedProp = $reflection->getProperty('started');
        $startedProp->setValue($this->scope, true);
        $idProp = $reflection->getProperty('id');
        $idProp->setValue($this->scope, 'test-session-id');

        $this->assertEquals('default_value', $this->scope->get('missing_key', 'default_value'));
    }

    #[Test]
    public function session_has_returns_true_for_existing_key() : void
    {
        $reflection  = new ReflectionClass($this->scope);
        $startedProp = $reflection->getProperty('started');
        $startedProp->setValue($this->scope, true);
        $idProp = $reflection->getProperty('id');
        $idProp->setValue($this->scope, 'test-session-id');

        $this->scope->set('key', 'value');
        $this->assertTrue($this->scope->has('key'));
    }

    #[Test]
    public function session_has_returns_false_for_missing_key() : void
    {
        $reflection  = new ReflectionClass($this->scope);
        $startedProp = $reflection->getProperty('started');
        $startedProp->setValue($this->scope, true);
        $idProp = $reflection->getProperty('id');
        $idProp->setValue($this->scope, 'test-session-id');

        $this->assertFalse($this->scope->has('nonexistent'));
    }

    #[Test]
    public function session_forget_removes_key() : void
    {
        $reflection  = new ReflectionClass($this->scope);
        $startedProp = $reflection->getProperty('started');
        $startedProp->setValue($this->scope, true);
        $idProp = $reflection->getProperty('id');
        $idProp->setValue($this->scope, 'test-session-id');

        $this->scope->set('temp', 'value');
        $this->assertTrue($this->scope->has('temp'));

        $this->scope->forget('temp');
        $this->assertFalse($this->scope->has('temp'));
    }

    #[Test]
    public function session_clear_removes_all_keys() : void
    {
        $reflection  = new ReflectionClass($this->scope);
        $startedProp = $reflection->getProperty('started');
        $startedProp->setValue($this->scope, true);
        $idProp = $reflection->getProperty('id');
        $idProp->setValue($this->scope, 'test-session-id');

        $this->scope->set('key1', 'value1');
        $this->scope->set('key2', 'value2');

        $this->scope->clear();
        $this->assertEmpty($this->scope->all());
    }

    #[Test]
    public function session_all_returns_all_data() : void
    {
        $reflection  = new ReflectionClass($this->scope);
        $startedProp = $reflection->getProperty('started');
        $startedProp->setValue($this->scope, true);
        $idProp = $reflection->getProperty('id');
        $idProp->setValue($this->scope, 'test-session-id');

        $this->scope->set('name', 'John');
        $this->scope->set('email', 'john@example.com');

        $all = $this->scope->all();
        $this->assertArrayHasKey('name', $all);
        $this->assertArrayHasKey('email', $all);
    }

    #[Test]
    public function session_destroy_clears_data() : void
    {
        $reflection  = new ReflectionClass($this->scope);
        $startedProp = $reflection->getProperty('started');
        $startedProp->setValue($this->scope, true);
        $idProp = $reflection->getProperty('id');
        $idProp->setValue($this->scope, 'test-session-id');

        $this->scope->set('key', 'value');
        $this->scope->destroy();

        $this->assertFalse($this->scope->isStarted());
        $this->assertEquals('', $this->scope->id());
    }

    #[Test]
    public function session_id_returns_empty_string_when_not_started() : void
    {
        $this->assertEquals('', $this->scope->id());
    }

    #[Test]
    public function array_session_store_persists_data() : void
    {
        $store     = new ArraySessionStore();
        $sessionId = 'session-123';

        $store->write($sessionId, ['user' => 'John', 'role' => 'admin']);
        $data = $store->read($sessionId);

        $this->assertEquals('John', $data['user']);
        $this->assertEquals('admin', $data['role']);
    }

    #[Test]
    public function array_session_store_returns_empty_for_missing_session() : void
    {
        $store = new ArraySessionStore();
        $data  = $store->read('nonexistent');

        $this->assertEmpty($data);
    }

    #[Test]
    public function array_session_store_destroy_removes_session() : void
    {
        $store     = new ArraySessionStore();
        $sessionId = 'session-123';

        $store->write($sessionId, ['key' => 'value']);
        $store->destroy($sessionId);

        $data = $store->read($sessionId);
        $this->assertEmpty($data);
    }

    #[Test]
    public function flash_data_is_stored_in_session() : void
    {
        // Test the flash concept using scope directly
        $reflection  = new ReflectionClass($this->scope);
        $startedProp = $reflection->getProperty('started');
        $startedProp->setValue($this->scope, true);
        $idProp = $reflection->getProperty('id');
        $idProp->setValue($this->scope, 'test-session-id');

        $this->scope->set('_flash_next', ['message' => 'Hello']);

        $flashes = $this->scope->get('_flash_next', []);
        $this->assertEquals('Hello', $flashes['message']);
    }

    protected function setUp() : void
    {
        $this->store = new ArraySessionStore();
        $this->scope = new SessionScope(store: $this->store);
    }
}
