<?php

declare(strict_types=1);

namespace Avax\Auth\Tests\System;

use PHPUnit\Framework\TestCase;

final class ProductBoundaryTest extends TestCase
{
    private array $shippedCapabilities = [
        'Auth' => [
            'System/Auth.php',
            'System/AuthInterface.php',
        ],
        'Authentication' => [
            'System/Flow/Login',
            'System/Flow/ChangePassword',
            'System/Flow/Recover',
        ],
        'MFA' => [
            'System/Flow/Mfa',
        ],
        'OAuth' => [
            'System/Flow/OAuth',
        ],
        'Sessions' => [
            'System/Flow/Session',
            'System/Capability/Session',
        ],
    ];

    private array $notShippedCapabilities = [
        'Admin UI' => 'No UI folder exists',
        'SIEM' => 'No SIEM integration',
        'Email' => 'No email infrastructure',
    ];

    public function testAuthKernelIsShipped() : void
    {
        foreach ($this->shippedCapabilities as $capability => $paths) {
            foreach ($paths as $path) {
                $fullPath = dirname(__DIR__, 2) . '/' . $path;
                $this->assertFileExists(
                    message: "Auth kernel capability missing: $capability ($path)",
                    filename: $fullPath
                );
            }
        }
    }

    public function testFullPlatformIsNotShipped() : void
    {
        $root = dirname(__DIR__, 2);

        foreach ($this->notShippedCapabilities as $capability => $reason) {
            $uiPath = $root . '/System/Ui';
            $siemPath = $root . '/integrations/siem';
            $emailPath = $root . '/integrations/email';

            if ($capability === 'Admin UI') {
                $this->assertFalse(
                    is_dir($uiPath),
                    message: "Full platform - $capability should NOT be shipped"
                );
            }
            if ($capability === 'SIEM') {
                $this->assertFalse(
                    is_dir($siemPath),
                    message: "Full platform - $capability should NOT be shipped"
                );
            }
            if ($capability === 'Email') {
                $this->assertFalse(
                    is_dir($emailPath),
                    message: "Full platform - $capability should NOT be shipped"
                );
            }
        }
    }

    public function testProductBoundaryIsClear() : void
    {
        $root = dirname(__DIR__, 2);

        $boundaryDocs = [
            $root . '/docs/product-boundary.md',
            $root . '/docs/STATUS.md',
            $root . '/docs/upgrade-migration-guide.md',
            $root . '/docs/supported-deployment-profiles.md',
            $root . '/docs/choose-vs-external-idp.md',
        ];

        foreach ($boundaryDocs as $doc) {
            $this->assertFileExists(
                message: "Boundary documentation missing: $doc",
                filename: $doc
            );
        }
    }

    public function testAuthMergedArtifactIsNotCanonicalState() : void
    {
        $root = dirname(__DIR__, 2);
        $contents = file_get_contents($root . '/Auth.txt');

        self::assertIsString($contents);
        $this->assertStringContainsString('non-canonical merged artifact', $contents);
        $this->assertStringContainsString('docs/STATUS.md', $contents);
    }

    public function testIdentityKernelScope() : void
    {
        $root = dirname(__DIR__, 2);

        $tenantPath = $root . '/System/Capability/Tenant';
        $scimPath = $root . '/System/Flow/Scim';
        $federationPath = $root . '/System/Capability/Federation';

        $this->assertFileExists(
            message: "Identity kernel - Tenant capability missing",
            filename: $tenantPath
        );
        $this->assertFileExists(
            message: "Identity kernel - SCIM capability missing",
            filename: $scimPath
        );
        $this->assertFileExists(
            message: "Identity kernel - Federation capability missing",
            filename: $federationPath
        );
    }
}
