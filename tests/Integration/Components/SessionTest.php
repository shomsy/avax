<?php

declare(strict_types=1);

namespace Avax\Tests\Integration\Components;

use Avax\Components\HTTP\Session\System\Capabilities\Storage\ArraySessionStore;
use Avax\Components\HTTP\Session\System\Capabilities\Storage\SessionDriver;
use Avax\Tests\TestCase;

final class SessionTest extends TestCase
{
    public function test_session_driver_reads_written_data(): void
    {
        SessionDriver::setStore(store: new ArraySessionStore());

        SessionDriver::write('session-one', data: ['user_id' => 10]);

        self::assertSame(['user_id' => 10], SessionDriver::read('session-one'));
    }
}
