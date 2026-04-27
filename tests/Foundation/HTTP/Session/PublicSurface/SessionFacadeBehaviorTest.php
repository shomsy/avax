<?php

declare(strict_types=1);

namespace Avax\Tests\Foundation\HTTP\Session\PublicSurface;

use Avax\HTTP\Session\Session;
use Avax\HTTP\Session\SessionStore\ArraySessionStore;
use Avax\Tests\TestCase;

final class SessionFacadeBehaviorTest extends TestCase
{
    public function test_session_facade_supports_basic_store_flows() : void
    {
        $session = new Session(store: new ArraySessionStore());

        $session->put(key: 'user_id', value: 7);

        self::assertTrue($session->has(key: 'user_id'));
        self::assertSame(7, $session->get(key: 'user_id'));

        $session->forget(key: 'user_id');

        self::assertFalse($session->has(key: 'user_id'));
    }

    public function test_regenerate_id_changes_identifier() : void
    {
        $session = new Session(store: new ArraySessionStore());
        $before  = $session->getId();

        $session->regenerateId();

        self::assertNotSame($before, $session->getId());
    }
}
