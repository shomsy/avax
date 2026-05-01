<?php

declare(strict_types=1);

namespace Avax\Tests\Foundation\HTTP\Session\PublicSurface;

use Avax\Components\HTTP\Session\System\Capabilities\Storage\ArraySessionStore;
use Avax\Components\HTTP\Session\System\PublicSurface\Session;
use Avax\Components\HTTP\Session\System\PublicSurface\SessionInterface;
use Avax\Components\HTTP\Session\System\PublicSurface\SessionScope;
use Avax\Tests\TestCase;

final class SessionTest extends TestCase
{
    private function createSession() : Session
    {
        $scope = new SessionScope(new ArraySessionStore());

        return new Session($scope);
    }

    public function test_implements_session_interface() : void
    {
        $session = $this->createSession();
        $this->assertInstanceOf(SessionInterface::class, $session);
    }

    public function test_start_returns_bool() : void
    {
        $session = $this->createSession();
        $this->assertTrue($session->start());
        $this->assertTrue($session->isStarted());
    }

    public function test_id_is_generated_after_start() : void
    {
        $session = $this->createSession();
        $session->start();
        $this->assertNotEmpty($session->id());
    }

    public function test_put_and_get() : void
    {
        $session = $this->createSession();
        $session->start();
        $session->put('key', 'value');

        $this->assertSame('value', $session->get('key'));
    }

    public function test_set_and_has() : void
    {
        $session = $this->createSession();
        $session->start();
        $session->set('key', 'value');

        $this->assertTrue($session->has('key'));
        $this->assertFalse($session->has('missing'));
    }

    public function test_all_returns_data() : void
    {
        $session = $this->createSession();
        $session->start();
        $session->put('a', 1);
        $session->put('b', 2);

        $all = $session->all();
        $this->assertArrayHasKey('a', $all);
        $this->assertArrayHasKey('b', $all);
    }

    public function test_forget_removes_key() : void
    {
        $session = $this->createSession();
        $session->start();
        $session->put('key', 'value');
        $session->forget('key');

        $this->assertNull($session->get('key'));
    }

    public function test_clear_removes_all() : void
    {
        $session = $this->createSession();
        $session->start();
        $session->put('key', 'value');
        $session->clear();

        $this->assertNull($session->get('key'));
    }

    public function test_flush_alias_for_clear() : void
    {
        $session = $this->createSession();
        $session->start();
        $session->put('key', 'value');
        $session->flush();

        $this->assertNull($session->get('key'));
    }

    public function test_regenerate_changes_id() : void
    {
        $session = $this->createSession();
        $session->start();
        $oldId = $session->id();
        $result = $session->regenerate();

        $this->assertTrue($result);
        $this->assertNotSame($oldId, $session->id());
    }

    public function test_flash_messages() : void
    {
        $session = $this->createSession();
        $session->start();
        $session->flash('notice', 'hello');
        $session->ageFlash();

        $this->assertSame('hello', $session->getFlash('notice'));
    }

    public function test_destroy_ends_session() : void
    {
        $session = $this->createSession();
        $session->start();
        $session->put('key', 'value');
        $session->destroy();

        $this->assertFalse($session->isStarted());
    }

    public function test_save_persists_data() : void
    {
        $store = new ArraySessionStore();
        $scope = new SessionScope($store);
        $session = new Session($scope);
        $session->start();
        $session->put('key', 'value');
        $session->save();

        // After save, reading from store should return the data
        $this->assertNotEmpty($store->read($scope->id()));
    }
}
