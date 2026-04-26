<?php

declare(strict_types=1);

namespace components\Auth\Tests\Integration;

use components\Auth\Tests\Support\CanonicalIntegrationAnchorTestCase;

final class ChangePasswordFlowTest extends CanonicalIntegrationAnchorTestCase
{
    protected static function canonicalTestPath() : string
    {
        return dirname(path: __DIR__) . '/Flows/ChangePassword/ChangePasswordTest.php';
    }
}
