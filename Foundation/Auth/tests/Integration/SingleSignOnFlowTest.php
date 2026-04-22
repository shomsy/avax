<?php

declare(strict_types=1);

namespace Avax\Auth\Tests\Integration;

use Avax\Auth\Tests\Support\CanonicalIntegrationAnchorTestCase;

final class SingleSignOnFlowTest extends CanonicalIntegrationAnchorTestCase
{
    protected static function canonicalTestPath() : string
    {
        return dirname(path: __DIR__) . '/Flows/Federation/FederationFlowTest.php';
    }
}
