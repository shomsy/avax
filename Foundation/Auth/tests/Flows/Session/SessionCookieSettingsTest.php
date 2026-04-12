<?php

declare(strict_types=1);

namespace Avax\Auth\Tests\Flow\Session;

use Avax\Auth\System\Flow\Session\SessionCookieSettings;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for session cookie policy validation.
 */
final class SessionCookieSettingsTest extends TestCase
{
    public function testSameSiteNoneRequiresSecureCookies() : void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Cookie sameSite None requires a secure cookie.');

        new SessionCookieSettings(secure: false, sameSite: 'None');
    }
}
