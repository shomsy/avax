<?php

declare(strict_types=1);

namespace Avax\Components\Auth\Tests\Integration;

use Avax\Components\Auth\Tests\Support\CanonicalIntegrationAnchorTestCase;

final class TenantMembershipFlowTest extends CanonicalIntegrationAnchorTestCase
{
    protected static function canonicalTestPath() : string
    {
        return dirname(path: __DIR__) . '/Flows/Tenant/TenantFlowTest.php';
    }
}
