<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Auth\Tests\Integration;

use Avax\Auth\Tests\Support\CanonicalIntegrationAnchorTestCase;

final class MfaFlowTest extends CanonicalIntegrationAnchorTestCase
{
    protected static function canonicalTestPath() : string
    {
        return dirname(path: __DIR__) . '/Flows/Mfa/Enroll/MfaEnrollmentTest.php';
    }
}
