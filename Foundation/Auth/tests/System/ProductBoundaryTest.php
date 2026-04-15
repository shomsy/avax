<?php

declare(strict_types=1);

namespace Avax\Auth\Tests\System;

use PHPUnit\Framework\TestCase;

final class ProductBoundaryTest extends TestCase
{
    private array $shippedCapabilities
        = [
            'Auth'           => [
                'System/Auth.php',
                'System/AuthInterface.php',
            ],
            'Authentication' => [
                'System/Flow/Login',
                'System/Flow/ChangePassword',
                'System/Flow/Recover',
            ],
            'MFA'            => [
                'System/Flow/Mfa',
            ],
            'OAuth'          => [
                'System/Flow/OAuth',
            ],
            'Sessions'       => [
                'System/Flow/Session',
                'System/Capability/Session',
            ],
        ];

    private array $notShippedCapabilities
        = [
            'Admin UI' => 'No UI folder exists',
            'SIEM'     => 'No SIEM integration',
            'Email'    => 'No email infrastructure',
        ];

    public function testAuthKernelIsShipped() : void
    {
        foreach ($this->shippedCapabilities as $capability => $paths) {
            foreach ($paths as $path) {
                $fullPath = dirname(__DIR__, 2) . '/' . $path;
                $this->assertFileExists(
                    filename: $fullPath,
                    message : "Auth kernel capability missing: $capability ($path)"
                );
            }
        }
    }

    public function testFullPlatformIsNotShipped() : void
    {
        $root = dirname(__DIR__, 2);

        foreach ($this->notShippedCapabilities as $capability => $reason) {
            $uiPath    = $root . '/System/Ui';
            $siemPath  = $root . '/integrations/siem';
            $emailPath = $root . '/integrations/email';

            if ($capability === 'Admin UI') {
                $this->assertFalse(
                    condition: is_dir($uiPath),
                    message  : "Full platform - $capability should NOT be shipped"
                );
            }
            if ($capability === 'SIEM') {
                $this->assertFalse(
                    condition: is_dir($siemPath),
                    message  : "Full platform - $capability should NOT be shipped"
                );
            }
            if ($capability === 'Email') {
                $this->assertFalse(
                    condition: is_dir($emailPath),
                    message  : "Full platform - $capability should NOT be shipped"
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
                filename: $doc,
                message : "Boundary documentation missing: $doc"
            );
        }
    }

    public function testAuthMergedArtifactIsNotCanonicalState() : void
    {
        $root     = dirname(__DIR__, 2);
        $contents = file_get_contents($root . '/Auth.txt');

        self::assertIsString(actual: $contents);
        $this->assertStringContainsString(needle: 'non-canonical merged artifact', haystack: $contents);
        $this->assertStringContainsString(needle: 'docs/STATUS.md', haystack: $contents);
    }

    public function testIdentityKernelScope() : void
    {
        $root = dirname(__DIR__, 2);

        $tenantPath     = $root . '/System/Capability/Tenant';
        $scimPath       = $root . '/System/Flow/Scim';
        $federationPath = $root . '/System/Capability/Federation';

        $this->assertFileExists(
            filename: $tenantPath,
            message : "Identity kernel - Tenant capability missing"
        );
        $this->assertFileExists(
            filename: $scimPath,
            message : "Identity kernel - SCIM capability missing"
        );
        $this->assertFileExists(
            filename: $federationPath,
            message : "Identity kernel - Federation capability missing"
        );
    }
}
