<?php

declare(strict_types=1);

namespace Avax\Auth\Tests\Integrations\Release;

use Avax\Auth\Integrations\Release\CheckMigrationPath;
use Avax\Auth\Integrations\Release\CheckSourceTruth;
use Avax\Auth\Integrations\Release\CheckSystemShape;
use Avax\Auth\Integrations\Release\CreateReleaseProvenance;
use Avax\Auth\Integrations\Release\GenerateEvidenceBundle;
use Avax\Auth\Integrations\Release\GenerateReleaseSbom;
use Avax\Auth\Integrations\Release\GenerateRollbackEvidence;
use Avax\Auth\Integrations\Release\ReviewComposerDependencies;
use Avax\Auth\Integrations\Release\RunConformanceHarness;
use Avax\Auth\Integrations\Release\RunKeyCompromiseDrill;
use Avax\Auth\Integrations\Release\RunKeyRolloverDrill;
use Avax\Auth\Integrations\Release\ScanCommittedSecrets;
use Avax\Auth\Integrations\Release\SignReleaseArtifact;
use PHPUnit\Framework\TestCase;
use Random\RandomException;

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
        $root       = dirname(__DIR__, 3);
        $provenance = (new CreateReleaseProvenance())->execute(repositoryRoot: $root, validationCommands: ['php composer.phar test']);

        $this->assertSame(expected: 'avax/auth', actual: $provenance['package']);
        $this->assertArrayHasKey(key: 'git_commit', array: $provenance);
        $this->assertNotSame(expected: '', actual: $provenance['git_commit']);
        $this->assertSame(expected: ['php composer.phar test'], actual: $provenance['validation_commands']);
    }

    public function testRollbackEvidenceCapturesRollbackTargetAndArtifactDigests() : void
    {
        $root     = dirname(__DIR__, 3);
        $artifact = tempnam(sys_get_temp_dir(), 'auth-rollback-');
        self::assertIsString(actual: $artifact);
        file_put_contents($artifact, 'rollback-artifact');

        $evidence = (new GenerateRollbackEvidence())->execute(
            repositoryRoot    : $root,
            rollbackTarget    : 'HEAD',
            artifacts         : [$artifact],
            validationCommands: ['php composer.phar test']
        );

        $this->assertSame(expected: 'avax/auth', actual: $evidence['package']);
        $this->assertTrue(condition: $evidence['rollback_ready']);
        $this->assertNotNull(actual: $evidence['rollback_target']);
        $this->assertSame(expected: hash_file('sha256', $artifact), actual: $evidence['artifacts'][0]['sha256']);
    }

    /**
     * @throws RandomException
     */
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
        $root   = dirname(__DIR__, 3);
        $review = (new ReviewComposerDependencies())->execute(
            composerLockPath: $root . '/composer.lock',
            policyPath      : $root . '/tooling/dependency-review-policy.json'
        );

        $this->assertTrue(condition: $review['approved']);
        $this->assertSame(expected: [], actual: $review['unreviewed_packages']);
        $this->assertSame(expected: [], actual: $review['unstable_packages']);
    }

    /**
     * @throws \JsonException
     */
    public function testDependencyReviewRejectsUnreviewedOrUnapprovedPackages() : void
    {
        $lockPath   = tempnam(sys_get_temp_dir(), 'auth-lock-');
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

    /**
     * @throws \JsonException
     */
    public function testKeyLifecycleDrillsRunAgainstKeyRingFiles() : void
    {
        $before         = $this->createKeyRingFile(configuration: [
                                                                      'primary'      => [
                                                                          'kid'       => '2026-04',
                                                                          'secret'    => 'secret-a',
                                                                          'algorithm' => 'HS256',
                                                                      ],
                                                                      'verification' => [],
                                                                  ]);
        $rollover       = $this->createKeyRingFile(configuration: [
                                                                      'primary'      => [
                                                                          'kid'       => '2026-05',
                                                                          'secret'    => 'secret-b',
                                                                          'algorithm' => 'HS256',
                                                                      ],
                                                                      'verification' => [
                                                                          [
                                                                              'kid'       => '2026-04',
                                                                              'secret'    => 'secret-a',
                                                                              'algorithm' => 'HS256',
                                                                          ],
                                                                      ],
                                                                  ]);
        $postCompromise = $this->createKeyRingFile(configuration: [
                                                                      'primary'      => [
                                                                          'kid'       => '2026-06',
                                                                          'secret'    => 'secret-c',
                                                                          'algorithm' => 'HS256',
                                                                      ],
                                                                      'verification' => [],
                                                                  ]);

        $rolloverResult   = (new RunKeyRolloverDrill())->execute(keyRingPath: $rollover);
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

    public function testConformanceHarnessReportsPassedAndFailedChecks() : void
    {
        $root   = dirname(__DIR__, 3);
        $report = (new RunConformanceHarness())->execute(
            repositoryRoot: $root,
            checks        : [
                                [
                                    'name'        => 'pass',
                                    'description' => 'Passing command',
                                    'command'     => [PHP_BINARY, '-r', 'exit(0);'],
                                ],
                                [
                                    'name'        => 'fail',
                                    'description' => 'Failing command',
                                    'command'     => [PHP_BINARY, '-r', 'exit(1);'],
                                ],
                            ]
        );

        $this->assertSame(expected: 'FAILED', actual: $report['overall']);
        $this->assertSame(expected: 1, actual: $report['summary']['passed']);
        $this->assertSame(expected: 1, actual: $report['summary']['failed']);
    }

    /**
     * @throws RandomException
     * @throws \JsonException
     */
    public function testEvidenceBundleTracksArtifactPresence() : void
    {
        $root = sys_get_temp_dir() . '/auth-evidence-' . bin2hex(random_bytes(4));
        mkdir($root);
        mkdir($root . '/build', 0777, true);
        file_put_contents($root . '/composer.json', json_encode(['name' => 'avax/auth'], JSON_THROW_ON_ERROR));
        file_put_contents($root . '/build/conformance-report.json', '{"ok":true}');

        $bundle = (new GenerateEvidenceBundle())->execute(
            repositoryRoot: $root,
            artifactPaths : [
                                'conformance' => 'build/conformance-report.json',
                                'sbom'        => 'build/sbom.json',
                            ]
        );

        $this->assertSame(expected: 'avax/auth', actual: $bundle['package']);
        $this->assertTrue(condition: $bundle['artifacts']['conformance']['exists']);
        $this->assertFalse(condition: $bundle['artifacts']['sbom']['exists']);
        $this->assertSame(expected: ['sbom'], actual: $bundle['missing_artifacts']);
    }

    /**
     * @throws RandomException
     * @throws \JsonException
     */
    public function testMigrationPathCheckDetectsLegacyReferencesAndMissingGuide() : void
    {
        $root = sys_get_temp_dir() . '/auth-migration-' . bin2hex(random_bytes(4));
        mkdir($root);
        mkdir($root . '/System/Configuration', 0777, true);
        mkdir($root . '/tests/System', 0777, true);
        file_put_contents($root . '/composer.json', json_encode(['name' => 'avax/auth'], JSON_THROW_ON_ERROR));
        file_put_contents($root . '/System/Configuration/Legacy.php', "<?php\nuse Avax\\Auth\\System\\Configuration\\AuthServiceProvider;\n");

        $result = (new CheckMigrationPath())->execute(repositoryRoot: $root);

        $this->assertFalse(condition: $result['clean']);
        $this->assertFalse(condition: $result['migration_documented']);
        $this->assertFalse(condition: $result['automated_upgrade_test']);
        $this->assertSame(expected: ['System/Configuration/Legacy.php'], actual: $result['legacy_namespace_references']);
    }

    /**
     * @throws RandomException
     */
    public function testSourceTruthCheckRejectsContradictoryState() : void
    {
        $root = sys_get_temp_dir() . '/auth-source-truth-' . bin2hex(random_bytes(4));
        mkdir($root);
        mkdir($root . '/docs', 0777, true);
        mkdir($root . '/.agents/management/evidence', 0777, true);
        file_put_contents($root . '/docs/STATUS.md', "# Status\n");
        file_put_contents($root . '/docs/product-boundary.md', "# Boundary\n");
        file_put_contents($root . '/docs/capability-matrix.md', "| device trust assessment | ⚠️ partial | boundary |\n");
        file_put_contents($root . '/docs/current-state.md', "This document is the canonical current-state summary for the Auth package.\n");
        file_put_contents($root . '/Auth.txt', "legacy mixed status\n");
        file_put_contents(
            $root . '/.agents/management/evidence/RISK_REGISTER.md',
            "still does not ship OIDC provider behavior, client-credentials, SCIM runtime\n"
        );

        $result = (new CheckSourceTruth())->execute(repositoryRoot: $root);

        $this->assertFalse(condition: $result['approved']);
        $this->assertNotEmpty(actual: $result['issues']);
    }

    /**
     * @throws RandomException
     */
    public function testSourceTruthCheckRejectsMissingCapabilityEvidencePaths() : void
    {
        $root = sys_get_temp_dir() . '/auth-source-truth-evidence-' . bin2hex(random_bytes(4));
        mkdir($root);
        mkdir($root . '/docs', 0777, true);
        mkdir($root . '/.agents/management/evidence', 0777, true);
        file_put_contents($root . '/docs/STATUS.md', "This document is authoritative.\n");
        file_put_contents($root . '/docs/product-boundary.md', "# Boundary\n");
        file_put_contents($root . '/docs/current-state.md', "# Current State\n");
        file_put_contents($root . '/docs/upgrade-migration-guide.md', "# Migration\n");
        file_put_contents(
            $root . '/docs/capability-matrix.md',
            "| Capability | Status | Ownership | Evidence |\n"
            . "|---|---|---|---|\n"
            . "| demo capability | ✅ supported | kernel | `tests/Flows/MissingTest.php`, `System/Flow/Missing/` |\n"
        );
        file_put_contents($root . '/Auth.txt', "non-canonical merged artifact\n");
        file_put_contents($root . '/.agents/management/evidence/RISK_REGISTER.md', "# Risks\n");

        $result = (new CheckSourceTruth())->execute(repositoryRoot: $root);

        $this->assertFalse(condition: $result['approved']);
        $this->assertStringContainsString(
            needle  : "Capability matrix evidence path missing for 'demo capability': tests/Flows/MissingTest.php",
            haystack: implode("\n", $result['issues'])
        );
    }

    /**
     * @throws RandomException
     */
    public function testSourceTruthCheckIgnoresCapabilityMatrixSeparatorsAndLegendRows() : void
    {
        $root = sys_get_temp_dir() . '/auth-source-truth-legend-' . bin2hex(random_bytes(4));
        mkdir($root);
        mkdir($root . '/docs', 0777, true);
        mkdir($root . '/.agents/management/evidence', 0777, true);
        mkdir($root . '/System/Capability/PasswordHashing', 0777, true);
        mkdir($root . '/tests/Capabilities/PasswordHashing', 0777, true);
        file_put_contents($root . '/docs/STATUS.md', "This document is authoritative.\n");
        file_put_contents($root . '/docs/product-boundary.md', "# Boundary\n");
        file_put_contents($root . '/docs/current-state.md', "# Current State\n");
        file_put_contents($root . '/docs/upgrade-migration-guide.md', "# Migration\n");
        file_put_contents(
            $root . '/docs/capability-matrix.md',
            "# Capability Matrix\n\n"
            . "| Capability       | Status      | Ownership | Evidence |\n"
            . "|------------------|-------------|-----------|----------|\n"
            . "| password hashing | ✅ supported | kernel    | `System/Capability/PasswordHashing/`, `tests/Capabilities/PasswordHashing/` |\n\n"
            . "## Status Legend\n\n"
            . "| Symbol      | Meaning |\n"
            . "|-------------|---------|\n"
            . "| ✅ supported | Package owns the capability |\n"
        );
        file_put_contents($root . '/Auth.txt', "non-canonical merged artifact\n");
        file_put_contents($root . '/.agents/management/evidence/RISK_REGISTER.md', "# Risks\n");

        $result = (new CheckSourceTruth())->execute(repositoryRoot: $root);

        $this->assertTrue(condition: $result['approved'], message: implode("\n", $result['issues']));
    }

    /**
     * @throws RandomException
     */
    public function testSystemShapeCheckRejectsUnexpectedAndForbiddenDirectories() : void
    {
        $root = sys_get_temp_dir() . '/auth-shape-' . bin2hex(random_bytes(4));
        mkdir($root);
        mkdir($root . '/System/Flow', 0777, true);
        mkdir($root . '/System/Capability', 0777, true);
        mkdir($root . '/System/Configuration', 0777, true);
        mkdir($root . '/System/Foundation', 0777, true);
        mkdir($root . '/System/Actions', 0777, true);
        mkdir($root . '/System/Capability/Helpers', 0777, true);
        file_put_contents($root . '/System/Auth.php', "<?php\n");
        file_put_contents($root . '/System/AuthInterface.php', "<?php\n");

        $result = (new CheckSystemShape())->execute(repositoryRoot: $root);

        $this->assertFalse(condition: $result['approved']);
        $this->assertSame(expected: ['System/Actions'], actual: $result['unexpected_top_level']);
        $this->assertContains(needle: 'System/Actions', haystack: $result['forbidden_directories']);
        $this->assertContains(needle: 'System/Capability/Helpers', haystack: $result['forbidden_directories']);
    }
}
