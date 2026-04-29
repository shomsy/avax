<?php

declare(strict_types=1);

namespace Avax\Tests\Foundation\HTTP\Session\Security;

use Avax\HTTP\Session\SessionSecurity\SessionPolicy\AbsoluteLifetimePolicy;
use Avax\HTTP\Session\SessionSecurity\SessionPolicy\IdleTimeoutPolicy;
use Avax\HTTP\Session\SessionSecurity\SessionPolicy\SecureTransportPolicy;
use Avax\Tests\TestCase;

final class SessionPolicyTest extends TestCase
{
    public function test_idle_timeout_allows_within_timeout() : void
    {
        $policy = new IdleTimeoutPolicy(timeout: 1800);

        $result = $policy->evaluate(context: [
                                        'last_activity' => time() - 600,
                                    ]);

        $this->assertTrue($result);
    }

    public function test_idle_timeout_rejects_after_timeout() : void
    {
        $policy = new IdleTimeoutPolicy(timeout: 1800);

        $result = $policy->evaluate(context: [
                                        'last_activity' => time() - 3600,
                                    ]);

        $this->assertFalse($result);
    }

    public function test_absolute_lifetime_allows_within_lifetime() : void
    {
        $policy = new AbsoluteLifetimePolicy(lifetime: 86400);

        $result = $policy->evaluate(context: [
                                        'created_at' => time() - 3600,
                                    ]);

        $this->assertTrue($result);
    }

    public function test_absolute_lifetime_rejects_after_lifetime() : void
    {
        $policy = new AbsoluteLifetimePolicy(lifetime: 86400);

        $result = $policy->evaluate(context: [
                                        'created_at' => time() - 172800,
                                    ]);

        $this->assertFalse($result);
    }

    public function test_secure_transport_requires_https() : void
    {
        $policy = new SecureTransportPolicy(requireSsl: true);

        $this->assertFalse($policy->evaluate(context: ['secure' => false]));
        $this->assertTrue($policy->evaluate(context: ['secure' => true]));
    }
}