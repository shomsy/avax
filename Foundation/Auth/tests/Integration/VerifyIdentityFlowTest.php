<?php

declare(strict_types=1);

namespace Avax\Auth\Tests\Integration;

use Avax\Auth\Tests\Support\CanonicalIntegrationAnchorTestCase;

final class VerifyIdentityFlowTest extends CanonicalIntegrationAnchorTestCase
{
    protected static function canonicalTestPath() : string
    {
        return dirname(__DIR__) . '/Unit/System/Flows/VerifyIdentity/VerifyIdentityTest.php';
    }
}
