<?php

declare(strict_types=1);

namespace Avax\Tests\Foundation\HTTP\Session\PublicSurface;

use Avax\Components\HTTP\Session\System\Capabilities\Storage\ArraySessionStore;
use Avax\Components\HTTP\Session\System\PublicSurface\Session;
use Avax\Components\HTTP\Session\System\PublicSurface\SessionScope;
use Avax\Tests\TestCase;

final class SessionFacadeBehaviorTest extends TestCase
{
    public function test_session_supports_basic_store_flows() : void
    {
        $scope   = new SessionScope(new ArraySessionStore());
        $session = new Session($scope);
        $session->start();

        $session->put('user_id', 7);

        self::assertTrue($session->has('user_id'));
        self::assertSame(7, $session->get('user_id'));

        $session->forget('user_id');

        self::assertFalse($session->has('user_id'));
    }

    public function test_regenerate_changes_identifier() : void
    {
        $scope   = new SessionScope(new ArraySessionStore());
        $session = new Session($scope);
        $session->start();
        $before = $session->id();

        $session->regenerate();

        self::assertNotSame($before, $session->id());
    }
}
