<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\Identity\ExternalIdentity;

use Avax\Components\Identity\ExternalIdentity\System\Capabilities\OAuth\Elements\OAuthGrantType;
use PHPUnit\Framework\TestCase;

final class ExternalIdentityCapabilitiesTest extends TestCase
{
    public function test_oauth_grant_type_enum_values() : void
    {
        $this->assertSame('authorization_code', OAuthGrantType::AUTHORIZATION_CODE->value);
        $this->assertSame('client_credentials', OAuthGrantType::CLIENT_CREDENTIALS->value);
    }
}
