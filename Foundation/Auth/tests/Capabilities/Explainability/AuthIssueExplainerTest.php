<?php

declare(strict_types=1);

namespace Avax\Auth\Tests\Capabilities\Explainability;

use Avax\Auth\System\Capabilities\Diagnostics\Explainability\AuthIssueExplainer;
use PHPUnit\Framework\TestCase;

final class AuthIssueExplainerTest extends TestCase
{
    public function testExplainerProvidesStructuredAccessDeniedExplanation() : void
    {
        $explanation = (new AuthIssueExplainer())->explainAccessDenied(
            resource          : 'tenant_security_change',
            requiredPermission: 'tenant.security.approve',
            tenant            : 'acme',
            resourceTenant    : 'acme'
        );

        $this->assertSame(expected: 'access_denied', actual: $explanation->code);
        $this->assertSame(expected: 'tenant.security.approve', actual: $explanation->context['required_permission']);
        $this->assertNotEmpty(actual: $explanation->resolution);
    }

    public function testExplainerMakesTrustedDeviceNonGoalExplicit() : void
    {
        $explanation = (new AuthIssueExplainer())->explainTrustedDeviceDecision(deviceId: 'device-1');

        $this->assertSame(expected: 'trusted_device_not_supported', actual: $explanation->code);
        $this->assertStringContainsString(needle: 'not provided by this package', haystack: $explanation->message);
        $this->assertSame(expected: 'device-1', actual: $explanation->context['device_id']);
    }
}
