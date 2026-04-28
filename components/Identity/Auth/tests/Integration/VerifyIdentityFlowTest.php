<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Auth\Tests\Integration;

use Avax\Components\Identity\Auth\Tests\Support\CanonicalIntegrationAnchorTestCase;

final class VerifyIdentityFlowTest extends CanonicalIntegrationAnchorTestCase
{
    protected static function canonicalTestPath() : string
    {
        return dirname(path: __DIR__) . '/Unit/System/Flows/VerifyIdentity/VerifyIdentityTest.php';
    }
}
