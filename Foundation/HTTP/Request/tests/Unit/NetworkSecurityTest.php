<?php

declare(strict_types=1);

namespace Avax\HTTP\Request\Tests\Unit;

use Avax\HTTP\Request\ServerRequest\IncomingRequest\RequestHeaders\RequestHeaders;
use Avax\HTTP\Request\ServerRequest\Network\ParseForwardedAddresses;
use Avax\HTTP\Request\ServerRequest\Network\ResolveClientAddress;
use Avax\HTTP\Request\ServerRequest\Network\TrustedProxyPolicy;
use PHPUnit\Framework\TestCase;

class NetworkSecurityTest extends TestCase
{
    public function test_resolve_client_address_returns_remote_addr_when_remote_is_not_trusted()
    {
        $policy   = new TrustedProxyPolicy(trustedProxies: ['192.168.1.1']);
        $resolver = new ResolveClientAddress(
            proxyPolicy    : $policy,
            forwardedParser: new ParseForwardedAddresses
        );

        $headers = new RequestHeaders(headersInput: ['X-Forwarded-For' => '10.0.0.1']);
        $result  = $resolver->execute(remoteAddr: '203.0.113.1', headers: $headers);

        $this->assertEquals(expected: '203.0.113.1', actual: $result);
    }

    public function test_resolve_client_address_returns_first_non_trusted_ip_from_right_to_left_chain()
    {
        $policy   = new TrustedProxyPolicy(trustedProxies: ['192.168.1.1', '192.168.1.2']);
        $resolver = new ResolveClientAddress(
            proxyPolicy    : $policy,
            forwardedParser: new ParseForwardedAddresses
        );

        $headers = new RequestHeaders(headersInput: ['X-Forwarded-For' => '10.0.0.1, 192.168.1.2']);
        $result  = $resolver->execute(remoteAddr: '192.168.1.1', headers: $headers);

        $this->assertEquals(expected: '10.0.0.1', actual: $result);
    }

    public function test_trusted_proxy_policy_accepts_valid_cidr_match()
    {
        $policy = new TrustedProxyPolicy(trustedProxies: ['10.0.0.0/24']);
        $this->assertTrue(condition: $policy->isTrusted(ip: '10.0.0.50'));
        $this->assertFalse(condition: $policy->isTrusted(ip: '10.0.1.1'));
    }

    public function test_untrusted_proxy_with_spoofed_forwarded_header()
    {
        $policy   = new TrustedProxyPolicy(trustedProxies: ['192.168.1.1']);
        $resolver = new ResolveClientAddress(
            proxyPolicy    : $policy,
            forwardedParser: new ParseForwardedAddresses
        );

        $headers = new RequestHeaders(headersInput: [
                                                        'X-Forwarded-For' => '8.8.8.8, 10.0.0.1',
                                                    ]);
        $result  = $resolver->execute(remoteAddr: '192.168.1.100', headers: $headers);

        // Since 192.168.1.100 is NOT in trusted proxies (only 192.168.1.1 is),
        // we must ignore the spoofed forwarded header and return the untrusted remote IP.
        $this->assertEquals(expected: '192.168.1.100', actual: $result);
    }

    public function test_trusted_proxy_with_forwarded_chain()
    {
        $policy   = new TrustedProxyPolicy(trustedProxies: ['10.0.0.1', '10.0.0.2']);
        $resolver = new ResolveClientAddress(
            proxyPolicy    : $policy,
            forwardedParser: new ParseForwardedAddresses
        );

        $headers = new RequestHeaders(headersInput: [
                                                        'X-Forwarded-For' => '192.168.1.1, 10.0.0.2, 203.0.113.1',
                                                    ]);
        $result  = $resolver->execute(remoteAddr: '10.0.0.1', headers: $headers);

        $this->assertEquals(expected: '192.168.1.1', actual: $result);
    }

    public function test_invalid_ip_in_forwarded_header()
    {
        $policy   = new TrustedProxyPolicy(trustedProxies: ['10.0.0.1']);
        $resolver = new ResolveClientAddress(
            proxyPolicy    : $policy,
            forwardedParser: new ParseForwardedAddresses
        );

        $headers = new RequestHeaders(headersInput: [
                                                        'X-Forwarded-For' => 'invalid-ip, 192.168.1.1',
                                                    ]);
        $result  = $resolver->execute(remoteAddr: '10.0.0.1', headers: $headers);

        $this->assertEquals(expected: '10.0.0.1', actual: $result);
    }

    public function test_empty_forwarded_header()
    {
        $policy   = new TrustedProxyPolicy(trustedProxies: ['10.0.0.1']);
        $resolver = new ResolveClientAddress(
            proxyPolicy    : $policy,
            forwardedParser: new ParseForwardedAddresses
        );

        $headers = new RequestHeaders(headersInput: ['X-Forwarded-For' => '']);
        $result  = $resolver->execute(remoteAddr: '192.168.1.1', headers: $headers);

        $this->assertEquals(expected: '192.168.1.1', actual: $result);
    }

    public function test_direct_remote_addr_fallback()
    {
        $policy   = new TrustedProxyPolicy(trustedProxies: ['10.0.0.1']);
        $resolver = new ResolveClientAddress(
            proxyPolicy    : $policy,
            forwardedParser: new ParseForwardedAddresses
        );

        $headers = new RequestHeaders(headersInput: []);
        $result  = $resolver->execute(remoteAddr: '203.0.113.1', headers: $headers);

        $this->assertEquals(expected: '203.0.113.1', actual: $result);
    }

    public function test_malformed_cidr_handled_gracefully()
    {
        $policy = new TrustedProxyPolicy(trustedProxies: ['10.0.0.0/999']);

        $this->assertFalse(condition: $policy->isTrusted(ip: '10.0.0.1'));
    }

    public function test_wildcard_trusted_proxy()
    {
        $policy = new TrustedProxyPolicy(trustedProxies: ['0.0.0.0/0']);
        $this->assertTrue(condition: $policy->isTrusted(ip: '1.2.3.4'));
        $this->assertTrue(condition: $policy->isTrusted(ip: '192.168.1.1'));
    }
}
