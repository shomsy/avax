<?php

declare(strict_types=1);

namespace Avax\Auth\Tests\Integrations\Release;

use Avax\Auth\Integrations\Release\CreateReleaseProvenance;
use Avax\Auth\Integrations\Release\GenerateRollbackEvidence;
use Avax\Auth\Integrations\Release\GenerateReleaseSbom;
use Avax\Auth\Integrations\Release\ReviewComposerDependencies;
use Avax\Auth\Integrations\Release\RunKeyCompromiseDrill;
use Avax\Auth\Integrations\Release\RunKeyRolloverDrill;
use Avax\Auth\Integrations\Release\ScanCommittedSecrets;
use Avax\Auth\Integrations\Release\SignReleaseArtifact;
use PHPUnit\Framework\TestCase;

final class ReleaseToolingTest extends TestCase
{
    public function testSbomContainsPackageMetadata() : void
    {
        $root = dirname(__DIR__, 3);
        $sbom = (new GenerateReleaseSbom())->execute(composerJsonPath: $root . '/composer.json', composerLockPath: $root . '/composer.lock');

        $this->assertSame(expected: 'CycloneDX', actual: $sbom['bomFormat']);
        $this->assertSame(expected: 'avax/auth', actual: $sbom['metadata']['component']['name']);
    }

    public function testReleaseProvenanceCapturesRepositoryState() : void
    {
        $root = dirname(__DIR__, 3);
        $provenance = (new CreateReleaseProvenance())->execute(repositoryRoot: $root, validationCommands: ['php composer.phar test']);

        $this->assertSame(expected: 'avax/auth', actual: $provenance['package']);
        $this->assertArrayHasKey(key: 'git_commit', array: $provenance);
        $this->assertSame(expected: ['php composer.phar test'], actual: $provenance['validation_commands']);
    }

    public function testRollbackEvidenceCapturesRollbackTargetAndArtifactDigests() : void
    {
        $root = dirname(__DIR__, 3);
        $artifact = tempnam(sys_get_temp_dir(), 'auth-rollback-');
        self::assertIsString(actual: $artifact);
        file_put_contents($artifact, 'rollback-artifact');

        $evidence = (new GenerateRollbackEvidence())->execute(
            repositoryRoot     : $root,
            rollbackTarget     : 'HEAD',
            artifacts          : [$artifact],
            validationCommands : ['php composer.phar test']
        );

        $this->assertSame(expected: 'avax/auth', actual: $evidence['package']);
        $this->assertTrue(condition: $evidence['rollback_ready']);
        $this->assertNotNull(actual: $evidence['rollback_target']);
        $this->assertSame(expected: hash_file('sha256', $artifact), actual: $evidence['artifacts'][0]['sha256']);
    }

    public function testSecretScannerFindsCommittedSecretPatterns() : void
    {
        $directory = sys_get_temp_dir() . '/auth-secret-scan-' . bin2hex(random_bytes(4));
        mkdir($directory);
        file_put_contents($directory . '/keys.txt', 'AKIA' . "IOSFODNN7EXAMPLE\n");

        $findings = (new ScanCommittedSecrets())->execute(rootPath: $directory, ignoredDirectories: []);

        $this->assertCount(expectedCount: 1, haystack: $findings);
        $this->assertSame(expected: 'aws_access_key_id', actual: $findings[0]['pattern']);
    }

    public function testDependencyReviewApprovesCurrentLockPolicy() : void
    {
        $root = dirname(__DIR__, 3);
        $review = (new ReviewComposerDependencies())->execute(
            composerLockPath: $root . '/composer.lock',
            policyPath      : $root . '/tooling/dependency-review-policy.json'
        );

        $this->assertTrue(condition: $review['approved']);
        $this->assertSame(expected: [], actual: $review['unreviewed_packages']);
        $this->assertSame(expected: [], actual: $review['unstable_packages']);
    }

    public function testDependencyReviewRejectsUnreviewedOrUnapprovedPackages() : void
    {
        $lockPath = tempnam(sys_get_temp_dir(), 'auth-lock-');
        $policyPath = tempnam(sys_get_temp_dir(), 'auth-policy-');
        self::assertIsString(actual: $lockPath);
        self::assertIsString(actual: $policyPath);

        file_put_contents($lockPath, json_encode([
            'packages' => [[
                'name' => 'approved/package',
                'version' => '1.0.0',
                'dist' => ['url' => 'https://github.com/example/approved/archive/refs/tags/1.0.0.zip'],
            ]],
            'packages-dev' => [[
                'name' => 'new/plugin-package',
                'version' => 'dev-main',
                'type' => 'composer-plugin',
                'source' => ['url' => 'https://git.example.test/new/plugin-package.git'],
            ]],
        ], JSON_THROW_ON_ERROR));
        file_put_contents($policyPath, json_encode([
            'reviewed_packages' => ['approved/package'],
            'allowed_plugin_packages' => [],
            'allowed_source_hosts' => ['github.com'],
        ], JSON_THROW_ON_ERROR));

        $review = (new ReviewComposerDependencies())->execute(composerLockPath: $lockPath, policyPath: $policyPath);

        $this->assertFalse(condition: $review['approved']);
        $this->assertSame(expected: ['new/plugin-package'], actual: $review['unreviewed_packages']);
        $this->assertSame(expected: ['new/plugin-package@dev-main'], actual: $review['unstable_packages']);
        $this->assertSame(expected: ['new/plugin-package'], actual: $review['unapproved_plugin_packages']);
        $this->assertSame(expected: ['new/plugin-package@git.example.test'], actual: $review['packages_from_unapproved_hosts']);
    }

    public function testArtifactSignerProducesVerifiableSignature() : void
    {
        $artifact = tempnam(sys_get_temp_dir(), 'auth-artifact-');
        self::assertIsString(actual: $artifact);
        file_put_contents($artifact, 'release-artifact');

        $privateKey = openssl_pkey_new([
            'private_key_bits' => 2048,
            'private_key_type' => OPENSSL_KEYTYPE_RSA,
        ]);
        self::assertNotFalse(condition: $privateKey);
        openssl_pkey_export($privateKey, $privateKeyPem);
        $details = openssl_pkey_get_details($privateKey);
        self::assertIsArray(actual: $details);
        $publicKeyPem = $details['key'];

        $signature = (new SignReleaseArtifact())->execute(artifactPath: $artifact, privateKeyPem: $privateKeyPem);

        $verification = openssl_verify(
            'release-artifact',
            base64_decode($signature, true) ?: '',
            $publicKeyPem,
            OPENSSL_ALGO_SHA256
        );

        $this->assertSame(expected: 1, actual: $verification);
    }

    public function testKeyLifecycleDrillsRunAgainstKeyRingFiles() : void
    {
        $before = $this->createKeyRingFile(configuration: [
            'primary' => [
                'kid' => '2026-04',
                'secret' => 'secret-a',
                'algorithm' => 'HS256',
            ],
            'verification' => [],
        ]);
        $rollover = $this->createKeyRingFile(configuration: [
            'primary' => [
                'kid' => '2026-05',
                'secret' => 'secret-b',
                'algorithm' => 'HS256',
            ],
            'verification' => [
                [
                    'kid' => '2026-04',
                    'secret' => 'secret-a',
                    'algorithm' => 'HS256',
                ],
            ],
        ]);
        $postCompromise = $this->createKeyRingFile(configuration: [
            'primary' => [
                'kid' => '2026-06',
                'secret' => 'secret-c',
                'algorithm' => 'HS256',
            ],
            'verification' => [],
        ]);

        $rolloverResult = (new RunKeyRolloverDrill())->execute(keyRingPath: $rollover);
        $compromiseResult = (new RunKeyCompromiseDrill())->execute(preRotationKeyRingPath: $before, postCompromiseKeyRingPath: $postCompromise);

        $this->assertSame(expected: '2026-05', actual: $rolloverResult['issued_kid']);
        $this->assertTrue(condition: $rolloverResult['rollover_verified']);
        $this->assertTrue(condition: $compromiseResult['revoked_old_kid']);
    }

    /**
     * @param array<string, mixed> $configuration
     */
    private function createKeyRingFile(array $configuration) : string
    {
        $path = tempnam(sys_get_temp_dir(), 'auth-release-');
        self::assertIsString(actual: $path);
        file_put_contents($path, json_encode($configuration, JSON_THROW_ON_ERROR));

        return $path;
    }
}
