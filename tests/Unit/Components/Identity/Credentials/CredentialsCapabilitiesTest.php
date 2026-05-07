<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\Identity\Credentials;

use Avax\Components\Identity\Credentials\System\System\Capabilities\Mfa\Runtime\Enums\MfaMethod;
use PHPUnit\Framework\TestCase;

final class CredentialsCapabilitiesTest extends TestCase
{
    public function test_mfa_method_enum_values() : void
    {
        $this->assertSame('totp', MfaMethod::TOTP->value);
        $this->assertSame('backup_code', MfaMethod::BACKUP_CODE->value);
    }
}
