<?php

declare(strict_types=1);

namespace Avax\HTTP\Session\Tests\Session\Security;

use Avax\HTTP\Session\SessionSecurity\SessionPolicy\AbsoluteLifetimePolicy;
use Avax\HTTP\Session\SessionSecurity\SessionPolicy\IdleTimeoutPolicy;
use Avax\HTTP\Session\SessionSecurity\SessionPolicy\SecureTransportPolicy;
use Avax\Tests\TestCase;
use PHPUnit\Framework\TestCase;

final class SessionPolicyTest extends TestCase
{
    public function test_idle_timeout_allows_within_timeout() : void
    {
        $policy = new IdleTimeoutPolicy(1800);

        $result = $policy->evaluate([
                                        'last_activity' => time() - 600,
                                    ]);

        $this->assertTrue($result);
    }

    public function test_idle_timeout_rejects_after_timeout() : void
    {
        $policy = new IdleTimeoutPolicy(1800);

        $result = $policy->evaluate([
                                        'last_activity' => time() - 3600,
                                    ]);

        $this->assertFalse($result);
    }

    public function test_absolute_lifetime_allows_within_lifetime() : void
    {
        $policy = new AbsoluteLifetimePolicy(86400);

        $result = $policy->evaluate([
                                        'created_at' => time() - 3600,
                                    ]);

        $this->assertTrue($result);
    }

    public function test_absolute_lifetime_rejects_after_lifetime() : void
    {
        $policy = new AbsoluteLifetimePolicy(86400);

        $result = $policy->evaluate([
                                        'created_at' => time() - 172800,
                                    ]);

        $this->assertFalse($result);
    }

    public function test_secure_transport_requires_https() : void
    {
        $policy = new SecureTransportPolicy(true);

        $this->assertFalse($policy->evaluate(['secure' => false]));
        $this->assertTrue($policy->evaluate(['secure' => true]));
    }
}