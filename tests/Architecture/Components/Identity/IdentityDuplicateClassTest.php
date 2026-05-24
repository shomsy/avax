<?php

declare(strict_types=1);

namespace Avax\Tests\Architecture\Components\Identity;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * Detects duplicate capability/class declarations in Identity components.
 *
 * Classes with the same basename in different namespaces may indicate
 * accidental duplication. Known intentional duplicates are allow-listed.
 */
final class IdentityDuplicateClassTest extends TestCase
{
    private string $identityPath;

    protected function setUp(): void
    {
        $this->identityPath = dirname(__DIR__, 4) . '/components/Identity';
    }

    /**
     * Known intentional duplicates — these are acceptable.
     * Key = basename, value = explanation.
     */
    private const ALLOWED_DUPLICATES = [
        'User' => 'PublicSurface DTO vs Capability Entity vs Value Object (intentional layering)',
        'Identity' => 'Top-level facade vs Auth capability coordinator (intentional layering)',
        'Credentials' => 'Flow value object vs component PublicSurface (intentional)',
        'ExternalIdentity' => 'PublicSurface facade vs Capability (deprecated static facade pattern)',
        'TenantContext' => 'PublicSurface facade vs Capability (deprecated static facade pattern)',
        'Access' => 'PublicSurface facade vs Capability namespace (intentional)',
        'Tokens' => 'PublicSurface facade vs Capability namespace (intentional)',
        'AuthorizationCodeStore' => 'OAuth capability vs Tokens capability (different domains)',
        'Security' => 'Tenancy capability vs Security sub-component (different domains)',
        'JwtSigner' => 'Signing vs Verification namespace (same concern, different role)',
        'AccessToken' => 'JwtAuth Tokens namespace vs Auth Tokens capability (different domains)',
        'RefreshToken' => 'JwtAuth Tokens namespace vs Auth Tokens capability (different domains)',
        'AuthorizationCodeRecord' => 'Tokens capability vs OAuth capability (different domains)',
        'InMemoryAuthorizationCodeStore' => 'Tokens capability vs OAuth capability (different domains)',
        'ExchangeAuthorizationCode' => 'Tokens Flow vs OAuth Capability (different domains)',
        'IntrospectToken' => 'Tokens Flow vs OAuth Capability (different domains)',
        'RevokeToken' => 'Tokens Flow vs OAuth Capability (different domains)',
        'PermissionDenied' => 'Capability exception vs Foundation exception (layering)',
        'Authorization' => 'Facade vs Capability (intentional layering)',
        'BeginAdminElevation' => 'Access Flow vs Tenancy Capability (different domains)',
        'EndAdminElevation' => 'Access Flow vs Tenancy Capability (different domains)',
        'UserId' => 'Capability VO vs Foundation Id (layering)',
        'SessionLifetime' => 'Capability VO vs Configuration value (layering)',
        'ReadCurrentUser' => 'Auth Flow vs CheckAuthentication Flow (different flow contexts)',
        'Logout' => 'Auth Flow vs OIDC Capability (different domains)',
        'Clock' => 'Foundation class vs Foundation/Time class (duplicate — needs resolution)',
        'ExternalIdentityException' => 'Auth Foundation vs ExternalIdentity Foundation (layering)',
        'Tenancy' => 'Capability vs PublicSurface (intentional layering)',
    ];

    #[Test]
    public function no_unexpected_duplicate_classes(): void
    {
        $basenames = [];
        $violations = [];

        $iterator = new \RecursiveDirectoryIterator($this->identityPath);
        $recursive = new \RecursiveIteratorIterator($iterator, \RecursiveIteratorIterator::LEAVES_ONLY);

        foreach ($recursive as $file) {
            if (!$file->isFile() || $file->getExtension() !== 'php') {
                continue;
            }

            $basename = $file->getBasename('.php');

            // Skip test helpers and interfaces
            if (str_ends_with($basename, 'Test') || str_ends_with($basename, 'Interface')) {
                continue;
            }

            $content = file_get_contents($file->getPathname());
            if ($content === false) {
                continue;
            }

            if (!preg_match('/^namespace\s+([^;]+);/m', $content, $nsMatch)) {
                continue;
            }

            // Only track actual class/enum declarations, not traits
            if (!preg_match('/^\s*(?:final\s+)?(?:abstract\s+)?(?:readonly\s+)?(?:class|enum)\s+' . preg_quote($basename, '/') . '/m', $content)) {
                continue;
            }

            $namespace = $nsMatch[1];
            $fullQualified = $namespace . '\\' . $basename;

            if (!isset($basenames[$basename])) {
                $basenames[$basename] = [];
            }

            $basenames[$basename][] = $fullQualified;
        }

        // Check for duplicates
        foreach ($basenames as $basename => $namespaces) {
            if (count($namespaces) < 2) {
                continue;
            }

            if (isset(self::ALLOWED_DUPLICATES[$basename])) {
                continue;
            }

            $violations[] = sprintf(
                "%s declared in:\n  %s\n  (allowed duplicates: %s)",
                $basename,
                implode("\n  ", $namespaces),
                implode(', ', array_keys(self::ALLOWED_DUPLICATES)),
            );
        }

        $this->assertEmpty(
            $violations,
            "Unexpected duplicate class declarations found:\n" . implode("\n\n", $violations),
        );
    }

    #[Test]
    public function documented_duplicate_classification(): void
    {
        $basenames = [];

        $iterator = new \RecursiveDirectoryIterator($this->identityPath);
        $recursive = new \RecursiveIteratorIterator($iterator, \RecursiveIteratorIterator::LEAVES_ONLY);

        foreach ($recursive as $file) {
            if (!$file->isFile() || $file->getExtension() !== 'php') {
                continue;
            }

            $basename = $file->getBasename('.php');

            if (str_ends_with($basename, 'Test') || str_ends_with($basename, 'Interface')) {
                continue;
            }

            $content = file_get_contents($file->getPathname());
            if ($content === false) {
                continue;
            }

            if (!preg_match('/^namespace\s+([^;]+);/m', $content, $nsMatch)) {
                continue;
            }

            if (!preg_match('/^\s*(?:final\s+)?(?:abstract\s+)?(?:readonly\s+)?(?:class|enum)\s+' . preg_quote($basename, '/') . '/m', $content)) {
                continue;
            }

            $namespace = $nsMatch[1];
            $fullQualified = $namespace . '\\' . $basename;

            if (!isset($basenames[$basename])) {
                $basenames[$basename] = [];
            }

            $basenames[$basename][] = $fullQualified;
        }

        // Collect allowed duplicates that actually exist
        $classified = [];

        foreach ($basenames as $basename => $namespaces) {
            if (count($namespaces) < 2) {
                continue;
            }

            if (isset(self::ALLOWED_DUPLICATES[$basename])) {
                $classified[] = sprintf(
                    '%s (%s) — %s',
                    $basename,
                    implode(', ', $namespaces),
                    self::ALLOWED_DUPLICATES[$basename],
                );
            }
        }

        // Informational — always passes, documents the classification
        $this->assertNotEmpty($classified, 'No duplicate classes found to classify');
    }
}
