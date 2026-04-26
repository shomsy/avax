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
use Avax\Tests\TestCase;
use JsonException;
use PHPUnit\Framework\TestCase;
use Random\RandomException;

final class ReleaseToolingTest extends TestCase
{
    public function testSbomContainsPackageMetadata() : void
    {
        $root = dirname(path: __DIR__, levels: 3);
        $sbom = new GenerateReleaseSbom()->execute(composerJsonPath: $root . '/composer.json', composerLockPath: $root . '/composer.lock');

        $this->assertSame(expected: 'CycloneDX', actual: $sbom['bomFormat']);
        $this->assertSame(expected: 'avax/auth', actual: $sbom['metadata']['component']['name']);
    }

    public function testReleaseProvenanceCapturesRepositoryState() : void
    {
        $root       = dirname(path: __DIR__, levels: 3);
        $provenance = new CreateReleaseProvenance()->execute(repositoryRoot: $root, validationCommands: ['php composer.phar test']);

        $this->assertSame(expected: 'avax/auth', actual: $provenance['package']);
        $this->assertArrayHasKey(key: 'git_commit', array: $provenance);
        $this->assertNotSame(expected: '', actual: $provenance['git_commit']);
        $this->assertSame(expected: ['php composer.phar test'], actual: $provenance['validation_commands']);
    }

    public function testRollbackEvidenceCapturesRollbackTargetAndArtifactDigests() : void
    {
        $root     = dirname(path: __DIR__, levels: 3);
        $artifact = tempnam(directory: sys_get_temp_dir(), prefix: 'auth-rollback-');
        self::assertIsString(actual: $artifact);
        file_put_contents(filename: $artifact, data: 'rollback-artifact');

        $evidence = new GenerateRollbackEvidence()->execute(
            repositoryRoot    : $root,
            rollbackTarget    : 'HEAD',
            artifacts         : [$artifact],
            validationCommands: ['php composer.phar test']
        );

        $this->assertSame(expected: 'avax/auth', actual: $evidence['package']);
        $this->assertTrue(condition: $evidence['rollback_ready']);
        $this->assertNotNull(actual: $evidence['rollback_target']);
        $this->assertSame(expected: hash_file(algo: 'sha256', filename: $artifact), actual: $evidence['artifacts'][0]['sha256']);
    }

    /**
     * @throws RandomException
     */
    public function testSecretScannerFindsCommittedSecretPatterns() : void
    {
        $directory = sys_get_temp_dir() . '/auth-secret-scan-' . bin2hex(string: random_bytes(length: 4));
        mkdir(directory: $directory);
        file_put_contents(filename: $directory . '/keys.txt', data: 'AKIA' . "IOSFODNN7EXAMPLE\n");

        $findings = new ScanCommittedSecrets()->execute(rootPath: $directory, ignoredDirectories: []);

        $this->assertCount(expectedCount: 1, haystack: $findings);
        $this->assertSame(expected: 'aws_access_key_id', actual: $findings[0]['pattern']);
    }

    public function testDependencyReviewApprovesCurrentLockPolicy() : void
    {
        $root   = dirname(path: __DIR__, levels: 3);
        $review = new ReviewComposerDependencies()->execute(
            composerLockPath: $root . '/composer.lock',
            policyPath      : $root . '/tooling/dependency-review-policy.json'
        );

        $this->assertTrue(condition: $review['approved']);
        $this->assertSame(expected: [], actual: $review['unreviewed_packages']);
        $this->assertSame(expected: [], actual: $review['unstable_packages']);
    }

    /**
     * @throws JsonException
     */
    public function testDependencyReviewRejectsUnreviewedOrUnapprovedPackages() : void
    {
        $lockPath   = tempnam(directory: sys_get_temp_dir(), prefix: 'auth-lock-');
        $policyPath = tempnam(directory: sys_get_temp_dir(), prefix: 'auth-policy-');
        self::assertIsString(actual: $lockPath);
        self::assertIsString(actual: $policyPath);

        file_put_contents(filename: $lockPath, data: json_encode(value: [
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
                                                 ],              flags: JSON_THROW_ON_ERROR));
        file_put_contents(filename: $policyPath, data: json_encode(value: [
                                                       'reviewed_packages' => ['approved/package'],
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                          'allowed_plugin_packages' => [],
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                'allowed_source_hosts' => ['github.com'],
                                                   ],              flags: JSON_THROW_ON_ERROR));

        $review = new ReviewComposerDependencies()->execute(composerLockPath: $lockPath, policyPath: $policyPath);

        $this->assertFalse(condition: $review['approved']);
        $this->assertSame(expected: ['new/plugin-package'], actual: $review['unreviewed_packages']);
        $this->assertSame(expected: ['new/plugin-package@dev-main'], actual: $review['unstable_packages']);
        $this->assertSame(expected: ['new/plugin-package'], actual: $review['unapproved_plugin_packages']);
        $this->assertSame(expected: ['new/plugin-package@git.example.test'], actual: $review['packages_from_unapproved_hosts']);
    }

    public function testArtifactSignerProducesVerifiableSignature() : void
    {
        $artifact = tempnam(directory: sys_get_temp_dir(), prefix: 'auth-artifact-');
        self::assertIsString(actual: $artifact);
        file_put_contents(filename: $artifact, data: 'release-artifact');

        $privateKey = openssl_pkey_new(options: [
                                           'private_key_bits' => 2048,
                                           'private_key_type' => OPENSSL_KEYTYPE_RSA,
                                       ]);
        self::assertNotFalse(condition: $privateKey);
        openssl_pkey_export(key: $privateKey, output: $privateKeyPem);
        $details = openssl_pkey_get_details(key: $privateKey);
        self::assertIsArray(actual: $details);
        $publicKeyPem = $details['key'];

        $signature = new SignReleaseArtifact()->execute(artifactPath: $artifact, privateKeyPem: $privateKeyPem);

        $verification = openssl_verify(
            data      : 'release-artifact',
            signature : base64_decode(string: $signature, strict: true) ?: '',
            public_key: $publicKeyPem,
            algorithm : OPENSSL_ALGO_SHA256
        );

        $this->assertSame(expected: 1, actual: $verification);
    }

    /**
     * @throws JsonException
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

        $rolloverResult   = new RunKeyRolloverDrill()->execute(keyRingPath: $rollover);
        $compromiseResult = new RunKeyCompromiseDrill()->execute(preRotationKeyRingPath: $before, postCompromiseKeyRingPath: $postCompromise);

        $this->assertSame(expected: '2026-05', actual: $rolloverResult['issued_kid']);
        $this->assertTrue(condition: $rolloverResult['rollover_verified']);
        $this->assertTrue(condition: $compromiseResult['revoked_old_kid']);
    }

    /**
     * @param array<string, mixed> $configuration
     *
     * @return string
     * @throws JsonException
     */
    private function createKeyRingFile(array $configuration) : string
    {
        $path = tempnam(directory: sys_get_temp_dir(), prefix: 'auth-release-');
        self::assertIsString(actual: $path);
        file_put_contents(filename: $path, data: json_encode(value: $configuration, flags: JSON_THROW_ON_ERROR));

        return $path;
    }

    public function testConformanceHarnessReportsPassedAndFailedChecks() : void
    {
        $root   = dirname(path: __DIR__, levels: 3);
        $report = new RunConformanceHarness()->execute(
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

    public function testConformanceHarnessDoesNotDeadlockOnHighOutputCommands() : void
    {
        $root   = dirname(path: __DIR__, levels: 3);
        $report = new RunConformanceHarness()->execute(
            repositoryRoot: $root,
            checks        : [[
                                 'name'        => 'noisy-pass',
                                 'description' => 'Passing command with enough output to fill pipe buffers',
                                 'command'     => [
                                     PHP_BINARY,
                                     '-r',
                                     'fwrite(STDOUT, str_repeat("out\n", 30000)); fwrite(STDERR, str_repeat("err\n", 30000)); exit(0);',
                                 ],
                             ]]
        );

        $this->assertSame(expected: 'PASSED', actual: $report['overall']);
        $this->assertSame(expected: 1, actual: $report['summary']['passed']);
        $this->assertSame(expected: 0, actual: $report['summary']['failed']);
    }

    public function testPhpUnitConfigurationDoesNotReferenceRemovedLegacyTestRoots() : void
    {
        $root     = dirname(path: __DIR__, levels: 3);
        $contents = file_get_contents(filename: $root . '/phpunit.xml.dist');

        self::assertIsString(actual: $contents);
        $configuration = simplexml_load_string(data: $contents);
        self::assertNotFalse(condition: $configuration);

        $directories = [];

        foreach ($configuration->testsuites->testsuite as $testsuite) {
            foreach ($testsuite->directory as $directory) {
                $directories[] = (string) $directory;
            }
        }

        $this->assertNotContains(needle: 'tests/Flow', haystack: $directories);
        $this->assertNotContains(needle: 'tests/Capability', haystack: $directories);
        $this->assertContains(needle: 'tests/Flows', haystack: $directories);
        $this->assertContains(needle: 'tests/System', haystack: $directories);
    }

    public function testIntegrationFlowAnchorFilesDeclarePhpUnitTestCases() : void
    {
        $root  = dirname(path: __DIR__, levels: 3);
        $files = glob(pattern: $root . '/tests/Integration/*FlowTest.php');

        self::assertIsArray(actual: $files);
        self::assertNotSame(expected: [], actual: $files);

        foreach ($files as $file) {
            $class = 'Avax\\Auth\\Tests\\Integration\\' . basename(path: $file, suffix: '.php');

            self::assertTrue(
                condition: class_exists(class: $class),
                message  : sprintf('Expected %s to declare %s.', $file, $class)
            );
            self::assertTrue(
                condition: is_subclass_of(object_or_class: $class, class: TestCase::class),
                message  : sprintf('Expected %s to extend %s.', $class, TestCase::class)
            );
        }
    }

    public function testProductionSourcesPassPhpStanAnalysisWithoutInternalErrors() : void
    {
        $root   = dirname(path: __DIR__, levels: 3);
        $report = new RunConformanceHarness()->execute(
            repositoryRoot: $root,
            checks        : [[
                                 'name'        => 'phpstan',
                                 'description' => 'Static analysis of production sources',
                                 'command'     => [
                                     PHP_BINARY,
                                     $root . '/vendor/bin/phpstan',
                                     'analyse',
                                     '--memory-limit=1G',
                                     '--no-progress',
                                     '--error-format=raw',
                                 ],
                             ]]
        );

        $this->assertSame(expected: 'PASSED', actual: $report['overall']);
        $this->assertSame(expected: 1, actual: $report['summary']['passed']);
        $this->assertSame(expected: 0, actual: $report['summary']['failed']);
    }

    public function testProductionSourcesPassStrictPhpStanAnalysis() : void
    {
        $root   = dirname(path: __DIR__, levels: 3);
        $report = new RunConformanceHarness()->execute(
            repositoryRoot: $root,
            checks        : [[
                                 'name'        => 'phpstan-strict',
                                 'description' => 'Strict static analysis of production sources',
                                 'command'     => [
                                     PHP_BINARY,
                                     $root . '/vendor/bin/phpstan',
                                     'analyse',
                                     '--memory-limit=1G',
                                     '-c',
                                     'phpstan.strict.neon',
                                     '--no-progress',
                                     '--error-format=raw',
                                 ],
                             ]]
        );

        $this->assertSame(expected: 'PASSED', actual: $report['overall']);
        $this->assertSame(expected: 1, actual: $report['summary']['passed']);
        $this->assertSame(expected: 0, actual: $report['summary']['failed']);
    }

    public function testRectorConfigurationExistsForDeterministicReleaseChecks() : void
    {
        $root     = dirname(path: __DIR__, levels: 3);
        $contents = file_get_contents(filename: $root . '/rector.php');

        self::assertIsString(actual: $contents);
        self::assertStringContainsString(needle: "__DIR__ . '/System'", haystack: $contents);
        self::assertStringContainsString(needle: "__DIR__ . '/integrations'", haystack: $contents);
        self::assertStringContainsString(needle: "__DIR__ . '/examples'", haystack: $contents);
        self::assertStringContainsString(needle: "__DIR__ . '/tooling'", haystack: $contents);
        self::assertStringNotContainsString(needle: "__DIR__ . '/tests'", haystack: $contents);
    }

    public function testReadmeQuickStartTracksStableBootstrapSeams() : void
    {
        $root     = dirname(path: __DIR__, levels: 3);
        $contents = file_get_contents(filename: $root . '/README.md');

        self::assertIsString(actual: $contents);
        self::assertStringContainsString(needle: '->withIdentityBackends(', haystack: $contents);
        self::assertStringContainsString(needle: '->withOidcProvider(', haystack: $contents);
        self::assertStringContainsString(needle: 'AuthServiceProvider::class', haystack: $contents);
        self::assertStringNotContainsString(needle: '->withIdentity(new Identity(', haystack: $contents);
    }

    /**
     * @throws RandomException
     * @throws JsonException
     */
    public function testEvidenceBundleTracksArtifactPresence() : void
    {
        $root = sys_get_temp_dir() . '/auth-evidence-' . bin2hex(string: random_bytes(length: 4));
        mkdir(directory: $root);
        mkdir(directory: $root . '/build', permissions: 0777, recursive: true);
        file_put_contents(filename: $root . '/composer.json', data: json_encode(value: ['name' => 'avax/auth'], flags: JSON_THROW_ON_ERROR));
        file_put_contents(filename: $root . '/build/conformance-report.json', data: '{"ok":true}');

        $bundle = new GenerateEvidenceBundle()->execute(
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
     * @throws JsonException
     */
    public function testMigrationPathCheckDetectsLegacyReferencesAndMissingGuide() : void
    {
        $root = sys_get_temp_dir() . '/auth-migration-' . bin2hex(string: random_bytes(length: 4));
        mkdir(directory: $root);
        mkdir(directory: $root . '/System/Configuration', permissions: 0777, recursive: true);
        mkdir(directory: $root . '/tests/System', permissions: 0777, recursive: true);
        file_put_contents(filename: $root . '/composer.json', data: json_encode(value: ['name' => 'avax/auth'], flags: JSON_THROW_ON_ERROR));
        file_put_contents(filename: $root . '/System/Configuration/Legacy.php', data: "<?php\nuse Avax\\Auth\\System\\Configuration\\AuthServiceProvider;\n");

        $result = new CheckMigrationPath()->execute(repositoryRoot: $root);

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
        $root = sys_get_temp_dir() . '/auth-source-truth-' . bin2hex(string: random_bytes(length: 4));
        mkdir(directory: $root);
        mkdir(directory: $root . '/docs', permissions: 0777, recursive: true);
        mkdir(directory: $root . '/.agents/management/evidence', permissions: 0777, recursive: true);
        file_put_contents(filename: $root . '/docs/STATUS.md', data: "# Status\n");
        file_put_contents(filename: $root . '/docs/product-boundary.md', data: "# Boundary\n");
        file_put_contents(filename: $root . '/docs/capability-matrix.md', data: "| device trust assessment | ⚠️ partial | boundary |\n");
        file_put_contents(filename: $root . '/docs/current-state.md', data: "This document is the canonical current-state summary for the Auth package.\n");
        file_put_contents(filename: $root . '/Auth.txt', data: "legacy mixed status\n");
        file_put_contents(
            filename: $root . '/.agents/management/evidence/RISK_REGISTER.md',
            data    : "still does not ship OIDC provider behavior, client-credentials, SCIM runtime\n"
        );

        $result = new CheckSourceTruth()->execute(repositoryRoot: $root);

        $this->assertFalse(condition: $result['approved']);
        $this->assertNotEmpty(actual: $result['issues']);
    }

    /**
     * @throws RandomException
     */
    public function testSourceTruthCheckRejectsMissingCapabilityEvidencePaths() : void
    {
        $root = sys_get_temp_dir() . '/auth-source-truth-evidence-' . bin2hex(string: random_bytes(length: 4));
        mkdir(directory: $root);
        mkdir(directory: $root . '/docs', permissions: 0777, recursive: true);
        mkdir(directory: $root . '/.agents/management/evidence', permissions: 0777, recursive: true);
        file_put_contents(filename: $root . '/docs/STATUS.md', data: "This document is authoritative.\n");
        file_put_contents(filename: $root . '/docs/product-boundary.md', data: "# Boundary\n");
        file_put_contents(filename: $root . '/docs/current-state.md', data: "# Current State\n");
        file_put_contents(filename: $root . '/docs/upgrade-migration-guide.md', data: "# Migration\n");
        file_put_contents(
            filename: $root . '/docs/capability-matrix.md',
            data    : "| Capability | Status | Ownership | Evidence |\n"
            . "|---|---|---|---|\n"
            . "| demo capability | ✅ supported | kernel | `tests/Flows/MissingTest.php`, `System/Flows/Missing/` |\n"
        );
        file_put_contents(filename: $root . '/Auth.txt', data: "non-canonical merged artifact\n");
        file_put_contents(filename: $root . '/.agents/management/evidence/RISK_REGISTER.md', data: "# Risks\n");
        $this->writeOwnershipHowThisWorksDocs(root: $root);

        $result = new CheckSourceTruth()->execute(repositoryRoot: $root);

        $this->assertFalse(condition: $result['approved']);
        $this->assertStringContainsString(
            needle  : "Capability matrix evidence path missing for 'demo capability': tests/Flows/MissingTest.php",
            haystack: implode(separator: "\n", array: $result['issues'])
        );
    }

    /**
     * @throws RandomException
     */
    public function testSourceTruthCheckIgnoresCapabilityMatrixSeparatorsAndLegendRows() : void
    {
        $root = sys_get_temp_dir() . '/auth-source-truth-legend-' . bin2hex(string: random_bytes(length: 4));
        mkdir(directory: $root);
        mkdir(directory: $root . '/docs', permissions: 0777, recursive: true);
        mkdir(directory: $root . '/.agents/management/evidence', permissions: 0777, recursive: true);
        mkdir(directory: $root . '/System/Capabilities/PasswordHashing', permissions: 0777, recursive: true);
        mkdir(directory: $root . '/tests/Capabilities/PasswordHashing', permissions: 0777, recursive: true);
        file_put_contents(filename: $root . '/docs/STATUS.md', data: "This document is authoritative.\n");
        file_put_contents(filename: $root . '/docs/product-boundary.md', data: "# Boundary\n");
        file_put_contents(filename: $root . '/docs/current-state.md', data: "# Current State\n");
        file_put_contents(filename: $root . '/docs/upgrade-migration-guide.md', data: "# Migration\n");
        file_put_contents(
            filename: $root . '/docs/capability-matrix.md',
            data    : "# Capability Matrix\n\n"
            . "| Capability       | Status      | Ownership | Evidence |\n"
            . "|------------------|-------------|-----------|----------|\n"
            . "| password hashing | ✅ supported | kernel    | `System/Capabilities/PasswordHashing/`, `tests/Capabilities/PasswordHashing/` |\n\n"
            . "## Status Legend\n\n"
            . "| Symbol      | Meaning |\n"
            . "|-------------|---------|\n"
            . "| ✅ supported | Package owns the capability |\n"
        );
        file_put_contents(filename: $root . '/Auth.txt', data: "non-canonical merged artifact\n");
        file_put_contents(filename: $root . '/.agents/management/evidence/RISK_REGISTER.md', data: "# Risks\n");
        $this->writeOwnershipHowThisWorksDocs(root: $root);

        $result = new CheckSourceTruth()->execute(repositoryRoot: $root);

        $this->assertTrue(condition: $result['approved'], message: implode(separator: "\n", array: $result['issues']));
    }

    /**
     * @throws RandomException
     */
    public function testSourceTruthCheckRejectsLegacySingularSystemRootsInCanonicalContracts() : void
    {
        $root = sys_get_temp_dir() . '/auth-source-truth-roots-' . bin2hex(string: random_bytes(length: 4));
        mkdir(directory: $root);
        mkdir(directory: $root . '/docs/architecture', permissions: 0777, recursive: true);
        mkdir(directory: $root . '/.agents/management/evidence', permissions: 0777, recursive: true);
        file_put_contents(filename: $root . '/AGENTS.md', data: "Source roots:\n- `System/Flow/`\n- `System/Capability/`\n");
        file_put_contents(filename: $root . '/docs/STATUS.md', data: "This document is authoritative.\n");
        file_put_contents(filename: $root . '/docs/product-boundary.md', data: "# Boundary\n");
        file_put_contents(filename: $root . '/docs/current-state.md', data: "# Current State\n");
        file_put_contents(filename: $root . '/docs/upgrade-migration-guide.md', data: "# Migration\n");
        file_put_contents(filename: $root . '/docs/capability-matrix.md', data: "# Capability Matrix\n");
        file_put_contents(filename: $root . '/docs/architecture/system-shape.md', data: "# Shape\n`System/Flow/`\n");
        file_put_contents(filename: $root . '/Auth.txt', data: "non-canonical merged artifact\n");
        file_put_contents(filename: $root . '/.agents/management/evidence/RISK_REGISTER.md', data: "# Risks\n");

        $result = new CheckSourceTruth()->execute(repositoryRoot: $root);

        $this->assertFalse(condition: $result['approved']);
        $this->assertContains(
            needle  : 'Legacy singular system root reference found in AGENTS.md: System/Flow/',
            haystack: $result['issues']
        );
        $this->assertContains(
            needle  : 'Legacy singular system root reference found in docs/architecture/system-shape.md: System/Flow/',
            haystack: $result['issues']
        );
    }

    /**
     * @throws RandomException
     */
    public function testSourceTruthCheckRejectsMissingOwnershipHowThisWorksPages() : void
    {
        $root = sys_get_temp_dir() . '/auth-source-truth-docs-' . bin2hex(string: random_bytes(length: 4));
        mkdir(directory: $root);
        mkdir(directory: $root . '/docs', permissions: 0777, recursive: true);
        mkdir(directory: $root . '/.agents/management/evidence', permissions: 0777, recursive: true);
        file_put_contents(filename: $root . '/docs/STATUS.md', data: "This document is authoritative.\n");
        file_put_contents(filename: $root . '/docs/product-boundary.md', data: "# Boundary\n");
        file_put_contents(filename: $root . '/docs/current-state.md', data: "# Current State\n");
        file_put_contents(filename: $root . '/docs/upgrade-migration-guide.md', data: "# Migration\n");
        file_put_contents(filename: $root . '/docs/capability-matrix.md', data: "# Capability Matrix\n");
        file_put_contents(filename: $root . '/Auth.txt', data: "non-canonical merged artifact\n");
        file_put_contents(filename: $root . '/.agents/management/evidence/RISK_REGISTER.md', data: "# Risks\n");

        $result = new CheckSourceTruth()->execute(repositoryRoot: $root);

        $this->assertFalse(condition: $result['approved']);
        $this->assertContains(
            needle  : 'Missing ownership how-this-works doc: docs/System/how-this-works.md',
            haystack: $result['issues']
        );
    }

    public function testSourceTruthCheckApprovesCurrentRepository() : void
    {
        $root   = dirname(path: __DIR__, levels: 3);
        $result = new CheckSourceTruth()->execute(repositoryRoot: $root);

        $this->assertTrue(condition: $result['approved'], message: implode(separator: "\n", array: $result['issues']));
    }

    /**
     * @throws RandomException
     */
    public function testSystemShapeCheckRejectsUnexpectedAndForbiddenDirectories() : void
    {
        $root = sys_get_temp_dir() . '/auth-shape-' . bin2hex(string: random_bytes(length: 4));
        mkdir(directory: $root);
        mkdir(directory: $root . '/System/Flow', permissions: 0777, recursive: true);
        mkdir(directory: $root . '/System/Capability', permissions: 0777, recursive: true);
        mkdir(directory: $root . '/System/Configuration', permissions: 0777, recursive: true);
        mkdir(directory: $root . '/System/Foundation', permissions: 0777, recursive: true);
        mkdir(directory: $root . '/System/Actions', permissions: 0777, recursive: true);
        mkdir(directory: $root . '/System/Capabilities/Helpers', permissions: 0777, recursive: true);
        file_put_contents(filename: $root . '/System/Auth.php', data: "<?php\n");
        file_put_contents(filename: $root . '/System/DefaultAuth.php', data: "<?php\n");

        $result = new CheckSystemShape()->execute(repositoryRoot: $root);

        $this->assertFalse(condition: $result['approved']);
        $this->assertSame(
            expected: ['System/Actions', 'System/Capability', 'System/Flow'],
            actual  : $result['unexpected_top_level']
        );
        $this->assertContains(needle: 'System/Actions', haystack: $result['forbidden_directories']);
        $this->assertContains(needle: 'System/Capabilities/Helpers', haystack: $result['forbidden_directories']);
    }

    /**
     * @throws RandomException
     */
    public function testSystemShapeCheckApprovesCanonicalPluralTree() : void
    {
        $root = sys_get_temp_dir() . '/auth-shape-canonical-' . bin2hex(string: random_bytes(length: 4));
        mkdir(directory: $root);
        foreach ([
            '/System/Flows/Login',
            '/System/Flows/Logout',
            '/System/Flows/Register',
            '/System/Flows/ChangePassword',
            '/System/Flows/ChangeEmail',
            '/System/Flows/RecoverAccess',
            '/System/Flows/VerifyIdentity',
            '/System/Flows/CheckAuthentication',
            '/System/Capabilities/Access',
            '/System/Capabilities/Identity',
            '/System/Capabilities/ExternalIdentity',
            '/System/Capabilities/IdentitySync',
            '/System/Capabilities/Tenancy',
            '/System/Capabilities/Diagnostics',
        ] as $directory) {
            mkdir(directory: $root . $directory, permissions: 0777, recursive: true);
        }
        mkdir(directory: $root . '/System/Configuration', permissions: 0777, recursive: true);
        mkdir(directory: $root . '/System/Foundation', permissions: 0777, recursive: true);
        file_put_contents(filename: $root . '/System/Auth.php', data: "<?php\n");
        file_put_contents(filename: $root . '/System/DefaultAuth.php', data: "<?php\n");

        $result = new CheckSystemShape()->execute(repositoryRoot: $root);

        $this->assertTrue(condition: $result['approved'], message: implode(separator: "\n", array: $result['issues']));
        $this->assertSame(expected: [], actual: $result['unexpected_top_level']);
        $this->assertSame(expected: [], actual: $result['forbidden_directories']);
    }

    /**
     * @throws RandomException
     */
    public function testSystemShapeCheckRejectsUnexpectedBoundaryDirectoriesInsideFlowsAndCapabilities() : void
    {
        $root = sys_get_temp_dir() . '/auth-shape-boundaries-' . bin2hex(string: random_bytes(length: 4));
        mkdir(directory: $root);

        foreach ([
            '/System/Flows/Login',
            '/System/Flows/Logout',
            '/System/Flows/Register',
            '/System/Flows/ChangePassword',
            '/System/Flows/ChangeEmail',
            '/System/Flows/RecoverAccess',
            '/System/Flows/VerifyIdentity',
            '/System/Flows/CheckAuthentication',
            '/System/Flows/OAuth',
            '/System/Capabilities/Access',
            '/System/Capabilities/Identity',
            '/System/Capabilities/ExternalIdentity',
            '/System/Capabilities/IdentitySync',
            '/System/Capabilities/Tenancy',
            '/System/Capabilities/Diagnostics',
            '/System/Capabilities/OAuth',
        ] as $directory) {
            mkdir(directory: $root . $directory, permissions: 0777, recursive: true);
        }

        mkdir(directory: $root . '/System/Configuration', permissions: 0777, recursive: true);
        mkdir(directory: $root . '/System/Foundation', permissions: 0777, recursive: true);
        file_put_contents(filename: $root . '/System/Auth.php', data: "<?php\n");
        file_put_contents(filename: $root . '/System/DefaultAuth.php', data: "<?php\n");

        $result = new CheckSystemShape()->execute(repositoryRoot: $root);

        $this->assertFalse(condition: $result['approved']);
        $this->assertSame(expected: ['System/Flows/OAuth'], actual: $result['unexpected_flow_top_level']);
        $this->assertSame(expected: ['System/Capabilities/OAuth'], actual: $result['unexpected_capability_top_level']);
    }

    /**
     * @throws RandomException
     */
    public function testSystemShapeCheckRejectsLegacySingularRoots() : void
    {
        $root = sys_get_temp_dir() . '/auth-shape-legacy-' . bin2hex(string: random_bytes(length: 4));
        mkdir(directory: $root);
        mkdir(directory: $root . '/System/Flow', permissions: 0777, recursive: true);
        mkdir(directory: $root . '/System/Capability', permissions: 0777, recursive: true);
        mkdir(directory: $root . '/System/Configuration', permissions: 0777, recursive: true);
        mkdir(directory: $root . '/System/Foundation', permissions: 0777, recursive: true);
        file_put_contents(filename: $root . '/System/Auth.php', data: "<?php\n");
        file_put_contents(filename: $root . '/System/DefaultAuth.php', data: "<?php\n");

        $result = new CheckSystemShape()->execute(repositoryRoot: $root);

        $this->assertFalse(condition: $result['approved']);
        $this->assertContains(needle: 'System/Capability', haystack: $result['unexpected_top_level']);
        $this->assertContains(needle: 'System/Flow', haystack: $result['unexpected_top_level']);
    }

    /**
     * @throws RandomException
     */
    public function testSystemShapeCheckRejectsLegacySymlinkAliases() : void
    {
        $root = sys_get_temp_dir() . '/auth-shape-links-' . bin2hex(string: random_bytes(length: 4));
        mkdir(directory: $root);
        mkdir(directory: $root . '/System/Flows', permissions: 0777, recursive: true);
        mkdir(directory: $root . '/System/Capabilities', permissions: 0777, recursive: true);
        mkdir(directory: $root . '/System/Configuration', permissions: 0777, recursive: true);
        mkdir(directory: $root . '/System/Foundation', permissions: 0777, recursive: true);
        file_put_contents(filename: $root . '/System/Auth.php', data: "<?php\n");
        file_put_contents(filename: $root . '/System/DefaultAuth.php', data: "<?php\n");
        symlink(target: $root . '/System/Flows', link: $root . '/System/Flow');
        symlink(target: $root . '/System/Capabilities', link: $root . '/System/Capability');

        $result = new CheckSystemShape()->execute(repositoryRoot: $root);

        $this->assertFalse(condition: $result['approved']);
        $this->assertContains(needle: 'System/Capability', haystack: $result['unexpected_top_level']);
        $this->assertContains(needle: 'System/Flow', haystack: $result['unexpected_top_level']);
    }

    private function writeOwnershipHowThisWorksDocs(string $root) : void
    {
        foreach ([
                     'docs/System/how-this-works.md',
                     'docs/System/Capabilities/how-this-works.md',
                     'docs/System/Capabilities/Access/how-this-works.md',
                     'docs/System/Capabilities/Diagnostics/how-this-works.md',
                     'docs/System/Capabilities/ExternalIdentity/how-this-works.md',
                     'docs/System/Capabilities/Identity/how-this-works.md',
                     'docs/System/Capabilities/IdentitySync/how-this-works.md',
                     'docs/System/Capabilities/Tenancy/how-this-works.md',
                     'docs/System/Configuration/how-this-works.md',
                     'docs/System/Flows/how-this-works.md',
                     'docs/System/Foundation/how-this-works.md',
                     'docs/integrations/how-this-works.md',
                     'docs/integrations/avax-container/how-this-works.md',
                     'docs/integrations/cookies/how-this-works.md',
                     'docs/integrations/diagnostics/how-this-works.md',
                     'docs/integrations/headers/how-this-works.md',
                     'docs/integrations/http/how-this-works.md',
                     'docs/integrations/release/how-this-works.md',
                 ] as $relativePath) {
            $fullPath = $root . '/' . $relativePath;
            mkdir(directory: dirname(path: $fullPath), permissions: 0777, recursive: true);
            file_put_contents(
                filename: $fullPath,
                data    : "---\n"
                . "title: test-how-this-works\n"
                . "owner: tests\n"
                . "last_reviewed: 2026-04-21\n"
                . "classification: internal\n"
                . "---\n\n"
                . "# Test Doc\n"
            );
        }
    }
}
