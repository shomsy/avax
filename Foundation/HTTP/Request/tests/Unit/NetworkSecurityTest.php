<?php

declare(strict_types=1);

namespace Avax\HTTP\Request\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Avax\HTTP\Request\IncomingHttp\Network\ResolveClientAddress;
use Avax\HTTP\Request\IncomingHttp\Network\TrustedProxyPolicy;
use Avax\HTTP\Request\IncomingHttp\IncomingRequest\RequestHeaders\RequestHeaders;

class NetworkSecurityTest extends TestCase
{
    public function test_resolve_client_address_returns_remote_addr_when_remote_is_not_trusted()
    {
        $policy = new TrustedProxyPolicy(trustedProxies: ['192.168.1.1']);
        $resolver = new ResolveClientAddress(proxyPolicy: $policy);
        
        $headers = new RequestHeaders(headers: ['X-Forwarded-For' => '10.0.0.1']);
        $result = $resolver->execute(remoteAddr: '203.0.113.1', headers: $headers);
        
        $this->assertEquals(expected: '203.0.113.1', actual: $result);
    }

    public function test_resolve_client_address_returns_first_non_trusted_ip_from_right_to_left_chain()
    {
        // 192.168.1.1 is the edge proxy (trusted)
        // 192.168.1.2 is an internal proxy (also trusted)
        $policy = new TrustedProxyPolicy(trustedProxies: ['192.168.1.1', '192.168.1.2']);
        $resolver = new ResolveClientAddress(proxyPolicy: $policy);
        
        // Chain: Client (10.0.0.1) -> Proxy1 (192.168.1.2) -> Proxy2 (192.168.1.1)
        $headers = new RequestHeaders(headers: ['X-Forwarded-For' => '10.0.0.1, 192.168.1.2']);
        $result = $resolver->execute(remoteAddr: '192.168.1.1', headers: $headers);
        
        $this->assertEquals(expected: '10.0.0.1', actual: $result);
    }

    public function test_trusted_proxy_policy_accepts_valid_cidr_match()
    {
        $policy = new TrustedProxyPolicy(trustedProxies: ['10.0.0.0/24']);
        $this->assertTrue(condition: $policy->isTrusted(ip: '10.0.0.50'));
        $this->assertFalse(condition: $policy->isTrusted(ip: '10.0.1.1'));
    }
}
