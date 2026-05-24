<?php

declare(strict_types=1);

namespace Avax\Tests\Architecture\Components\Identity;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * Verifies that all Identity sub-components follow the canonical AvaX component shape:
 * System/PublicSurface, System/Capabilities, System/Flows, System/Configuration.
 *
 * Foundation is optional. Forbidden directories are scanned separately.
 */
final class IdentityComponentStructureTest extends TestCase
{
    private string $componentsPath;

    protected function setUp(): void
    {
        $this->componentsPath = dirname(__DIR__, 4) . '/components';
    }

    #[Test]
    public function identity_auth_has_required_structure(): void
    {
        $path = $this->componentsPath . '/Identity/Auth';

        $this->assertDirectoryExists($path . '/System/PublicSurface');
        $this->assertDirectoryExists($path . '/System/Capabilities');
        $this->assertDirectoryExists($path . '/System/Flows');
        $this->assertDirectoryExists($path . '/System/Configuration');
    }

    #[Test]
    public function identity_access_has_required_structure(): void
    {
        $path = $this->componentsPath . '/Identity/Access';

        $this->assertDirectoryExists($path . '/System/PublicSurface');
        $this->assertDirectoryExists($path . '/System/Capabilities');
        $this->assertDirectoryExists($path . '/System/Flows');
        $this->assertDirectoryExists($path . '/System/Configuration');
    }

    #[Test]
    public function identity_credentials_has_required_structure(): void
    {
        $path = $this->componentsPath . '/Identity/Credentials';

        $this->assertDirectoryExists($path . '/System/PublicSurface');
        $this->assertDirectoryExists($path . '/System/Capabilities');
        $this->assertDirectoryExists($path . '/System/Flows');
        $this->assertDirectoryExists($path . '/System/Configuration');
    }

    #[Test]
    public function identity_external_identity_has_required_structure(): void
    {
        $path = $this->componentsPath . '/Identity/ExternalIdentity';

        $this->assertDirectoryExists($path . '/System/PublicSurface');
        $this->assertDirectoryExists($path . '/System/Capabilities');
        $this->assertDirectoryExists($path . '/System/Flows');
        $this->assertDirectoryExists($path . '/System/Configuration');
    }

    #[Test]
    public function identity_tenancy_has_required_structure(): void
    {
        $path = $this->componentsPath . '/Identity/Tenancy';

        $this->assertDirectoryExists($path . '/System/PublicSurface');
        $this->assertDirectoryExists($path . '/System/Capabilities');
        $this->assertDirectoryExists($path . '/System/Flows');
        $this->assertDirectoryExists($path . '/System/Configuration');
    }

    #[Test]
    public function identity_tokens_has_required_structure(): void
    {
        $path = $this->componentsPath . '/Identity/Tokens';

        $this->assertDirectoryExists($path . '/System/PublicSurface');
        $this->assertDirectoryExists($path . '/System/Capabilities');
        $this->assertDirectoryExists($path . '/System/Flows');
        $this->assertDirectoryExists($path . '/System/Configuration');
    }

    #[Test]
    public function identity_top_level_has_required_structure(): void
    {
        $path = $this->componentsPath . '/Identity';

        $this->assertDirectoryExists($path . '/System/PublicSurface');
        $this->assertDirectoryExists($path . '/System/Capabilities');
        $this->assertDirectoryExists($path . '/System/Flows');
        $this->assertDirectoryExists($path . '/System/Configuration');
    }

    /**
     * @return list<array{string}>
     */
    public static function identitySubComponents(): array
    {
        return [
            ['Identity/Auth'],
            ['Identity/Access'],
            ['Identity/Credentials'],
            ['Identity/ExternalIdentity'],
            ['Identity/Tenancy'],
            ['Identity/Tokens'],
        ];
    }

    #[Test]
    #[DataProvider('identitySubComponents')]
    public function identity_system_directories_contain_php_files(string $component): void
    {
        $systemPath = $this->componentsPath . '/' . $component . '/System';

        $this->assertDirectoryExists(
            $systemPath,
            "System directory should exist for {$component}",
        );

        $phpFiles = glob($systemPath . '/**/*.php', GLOB_NOSORT);
        $this->assertNotEmpty(
            $phpFiles,
            "System directory should contain PHP files for {$component}",
        );
    }

    #[Test]
    public function no_identity_component_uses_forbidden_directories(): void
    {
        $forbidden = ['Services', 'Helpers', 'Utils', 'Common', 'Shared', 'Managers', 'Core', 'Support'];

        $iterator = new \RecursiveDirectoryIterator($this->componentsPath . '/Identity');
        $recursive = new \RecursiveIteratorIterator($iterator, \RecursiveIteratorIterator::SELF_FIRST);

        $violations = [];

        foreach ($recursive as $file) {
            if ($file->isDir() && in_array($file->getBasename(), $forbidden, true)) {
                $violations[] = $file->getPathname();
            }
        }

        $this->assertEmpty(
            $violations,
            'Identity components must not use forbidden directories. Found: ' . implode(', ', $violations),
        );
    }
}
