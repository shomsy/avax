<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Auth\Tests\Integration;

use Avax\Components\Identity\Auth\Tests\Support\CanonicalIntegrationAnchorTestCase;

final class ChangePasswordFlowTest extends CanonicalIntegrationAnchorTestCase
{
    protected static function canonicalTestPath() : string
    {
        return dirname(path: __DIR__) . '/Flows/ChangePassword/ChangePasswordTest.php';
    }
}
