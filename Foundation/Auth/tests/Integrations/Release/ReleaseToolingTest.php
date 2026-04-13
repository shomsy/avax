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
        $sbom = (new GenerateReleaseSbom())->execute($root . '/composer.json', $root . '/composer.lock');

        $this->assertSame('CycloneDX', $sbom['bomFormat']);
        $this->assertSame('avax/auth', $sbom['metadata']['component']['name']);
    }

    public function testReleaseProvenanceCapturesRepositoryState() : void
    {
        $root = dirname(__DIR__, 3);
        $provenance = (new CreateReleaseProvenance())->execute($root, ['php composer.phar test']);

        $this->assertSame('avax/auth', $provenance['package']);
        $this->assertArrayHasKey('git_commit', $provenance);
        $this->assertSame(['php composer.phar test'], $provenance['validation_commands']);
    }

    public function testRollbackEvidenceCapturesRollbackTargetAndArtifactDigests() : void
    {
        $root = dirname(__DIR__, 3);
        $artifact = tempnam(sys_get_temp_dir(), 'auth-rollback-');
        self::assertIsString($artifact);
        file_put_contents($artifact, 'rollback-artifact');

        $evidence = (new GenerateRollbackEvidence())->execute(
            repositoryRoot     : $root,
            rollbackTarget     : 'HEAD',
            artifacts          : [$artifact],
            validationCommands : ['php composer.phar test']
        );

        $this->assertSame('avax/auth', $evidence['package']);
        $this->assertTrue($evidence['rollback_ready']);
        $this->assertNotNull($evidence['rollback_target']);
        $this->assertSame(hash_file('sha256', $artifact), $evidence['artifacts'][0]['sha256']);
    }

    public function testSecretScannerFindsCommittedSecretPatterns() : void
    {
        $directory = sys_get_temp_dir() . '/auth-secret-scan-' . bin2hex(random_bytes(4));
        mkdir($directory);
        file_put_contents($directory . '/keys.txt', 'AKIA' . "IOSFODNN7EXAMPLE\n");

        $findings = (new ScanCommittedSecrets())->execute($directory, []);

        $this->assertCount(1, $findings);
        $this->assertSame('aws_access_key_id', $findings[0]['pattern']);
    }

    public function testDependencyReviewApprovesCurrentLockPolicy() : void
    {
        $root = dirname(__DIR__, 3);
        $review = (new ReviewComposerDependencies())->execute(
            $root . '/composer.lock',
            $root . '/tooling/dependency-review-policy.json'
        );

        $this->assertTrue($review['approved']);
        $this->assertSame([], $review['unreviewed_packages']);
        $this->assertSame([], $review['unstable_packages']);
    }

    public function testDependencyReviewRejectsUnreviewedOrUnapprovedPackages() : void
    {
        $lockPath = tempnam(sys_get_temp_dir(), 'auth-lock-');
        $policyPath = tempnam(sys_get_temp_dir(), 'auth-policy-');
        self::assertIsString($lockPath);
        self::assertIsString($policyPath);

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

        $review = (new ReviewComposerDependencies())->execute($lockPath, $policyPath);

        $this->assertFalse($review['approved']);
        $this->assertSame(['new/plugin-package'], $review['unreviewed_packages']);
        $this->assertSame(['new/plugin-package@dev-main'], $review['unstable_packages']);
        $this->assertSame(['new/plugin-package'], $review['unapproved_plugin_packages']);
        $this->assertSame(['new/plugin-package@git.example.test'], $review['packages_from_unapproved_hosts']);
    }

    public function testArtifactSignerProducesVerifiableSignature() : void
    {
        $artifact = tempnam(sys_get_temp_dir(), 'auth-artifact-');
        self::assertIsString($artifact);
        file_put_contents($artifact, 'release-artifact');

        $privateKey = openssl_pkey_new([
            'private_key_bits' => 2048,
            'private_key_type' => OPENSSL_KEYTYPE_RSA,
        ]);
        self::assertNotFalse($privateKey);
        openssl_pkey_export($privateKey, $privateKeyPem);
        $details = openssl_pkey_get_details($privateKey);
        self::assertIsArray($details);
        $publicKeyPem = $details['key'];

        $signature = (new SignReleaseArtifact())->execute($artifact, $privateKeyPem);

        $verification = openssl_verify(
            'release-artifact',
            base64_decode($signature, true) ?: '',
            $publicKeyPem,
            OPENSSL_ALGO_SHA256
        );

        $this->assertSame(1, $verification);
    }

    public function testKeyLifecycleDrillsRunAgainstKeyRingFiles() : void
    {
        $before = $this->createKeyRingFile([
            'primary' => [
                'kid' => '2026-04',
                'secret' => 'secret-a',
                'algorithm' => 'HS256',
            ],
            'verification' => [],
        ]);
        $rollover = $this->createKeyRingFile([
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
        $postCompromise = $this->createKeyRingFile([
            'primary' => [
                'kid' => '2026-06',
                'secret' => 'secret-c',
                'algorithm' => 'HS256',
            ],
            'verification' => [],
        ]);

        $rolloverResult = (new RunKeyRolloverDrill())->execute($rollover);
        $compromiseResult = (new RunKeyCompromiseDrill())->execute($before, $postCompromise);

        $this->assertSame('2026-05', $rolloverResult['issued_kid']);
        $this->assertTrue($rolloverResult['rollover_verified']);
        $this->assertTrue($compromiseResult['revoked_old_kid']);
    }

    /**
     * @param array<string, mixed> $configuration
     */
    private function createKeyRingFile(array $configuration) : string
    {
        $path = tempnam(sys_get_temp_dir(), 'auth-release-');
        self::assertIsString($path);
        file_put_contents($path, json_encode($configuration, JSON_THROW_ON_ERROR));

        return $path;
    }
}
