<?php

declare(strict_types=1);

namespace Avax\Auth\Tests\System;

use Avax\Auth\System\Capabilities\ExternalIdentity\ExternalIdentity;
use Avax\Auth\System\Capabilities\ExternalIdentity\OAuth\OAuth;
use Avax\Auth\System\Capabilities\ExternalIdentity\OpenIDConnect\OpenIDConnect;
use Avax\Auth\System\Capabilities\ExternalIdentity\SingleSignOn\SingleSignOn;
use Avax\Auth\System\Capabilities\Identity\Identity;
use Avax\Auth\System\Capabilities\Identity\IdentityOwners\Account;
use Avax\Auth\System\Capabilities\Identity\IdentityOwners\Authentication;
use Avax\Auth\System\Capabilities\Identity\IdentityOwners\Recovery;
use Avax\Auth\System\Capabilities\Identity\IdentityOwners\Verification;
use Avax\Auth\System\Capabilities\Identity\Jwt\JwtIdentityInterface;
use Avax\Auth\System\Capabilities\Identity\Mfa\Mfa;
use Avax\Auth\System\Capabilities\Identity\Passkey\Passkey;
use Avax\Auth\System\Capabilities\Identity\Session\SessionIdentityInterface;
use Avax\Auth\System\Capabilities\Identity\Sessions\Sessions;
use Avax\Auth\System\Capabilities\IdentitySync\IdentitySync;
use Avax\Auth\System\Capabilities\IdentitySync\Provisioning\Provisioning;
use Avax\Auth\System\Capabilities\IdentitySync\SCIM\SCIM;
use Avax\Auth\System\Capabilities\Tenancy\Security\Security;
use Avax\Auth\System\Capabilities\Tenancy\Tenancy;
use Avax\Auth\System\Capabilities\Tenancy\Tenants\Tenants;
use Avax\Tests\TestCase;
use FilesystemIterator;
use PHPUnit\Framework\TestCase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use ReflectionClass;
use ReflectionException;
use ReflectionNamedType;
use ReflectionParameter;
use SplFileInfo;

final class ProductBoundaryTest extends TestCase
{
    private array $shippedCapabilities
        = [
            'Auth'           => [
                'System/Auth.php',
                'System/AuthInterface.php',
            ],
            'Authentication' => [
                'System/Flows/Login',
                'System/Flows/ChangePassword',
                'System/Flows/RecoverAccess',
            ],
            'MFA'            => [
                'System/Capabilities/Identity/Mfa',
            ],
            'OAuth'          => [
                'System/Capabilities/ExternalIdentity/OAuth',
            ],
            'Sessions'       => [
                'System/Capabilities/Identity/Sessions',
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
                $fullPath = dirname(path: __DIR__, levels: 2) . '/' . $path;
                $this->assertFileExists(
                    filename: $fullPath,
                    message : "Auth kernel capability missing: {$capability} ({$path})"
                );
            }
        }
    }

    public function testFullPlatformIsNotShipped() : void
    {
        $root = dirname(path: __DIR__, levels: 2);

        foreach ($this->notShippedCapabilities as $capability => $reason) {
            $uiPath    = $root . '/System/Ui';
            $siemPath  = $root . '/integrations/siem';
            $emailPath = $root . '/integrations/email';

            if ($capability === 'Admin UI') {
                $this->assertFalse(
                    condition: is_dir(filename: $uiPath),
                    message  : "Full platform - {$capability} should NOT be shipped"
                );
            }
            if ($capability === 'SIEM') {
                $this->assertFalse(
                    condition: is_dir(filename: $siemPath),
                    message  : "Full platform - {$capability} should NOT be shipped"
                );
            }
            if ($capability === 'Email') {
                $this->assertFalse(
                    condition: is_dir(filename: $emailPath),
                    message  : "Full platform - {$capability} should NOT be shipped"
                );
            }
        }
    }

    public function testProductBoundaryIsClear() : void
    {
        $root = dirname(path: __DIR__, levels: 2);

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
                message : "Boundary documentation missing: {$doc}"
            );
        }
    }

    public function testAuthMergedArtifactIsNotCanonicalState() : void
    {
        $root     = dirname(path: __DIR__, levels: 2);
        $contents = file_get_contents(filename: $root . '/Auth.txt');

        self::assertIsString(actual: $contents);
        $this->assertStringContainsString(needle: 'non-canonical merged artifact', haystack: $contents);
        $this->assertStringContainsString(needle: 'docs/STATUS.md', haystack: $contents);
    }

    public function testIdentityKernelScope() : void
    {
        $root = dirname(path: __DIR__, levels: 2);

        $tenantPath     = $root . '/System/Capabilities/Tenancy';
        $scimPath       = $root . '/System/Capabilities/IdentitySync/SCIM';
        $federationPath = $root . '/System/Capabilities/ExternalIdentity/SingleSignOn';

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

    public function testCanonicalBoundaryDirectoriesExist() : void
    {
        $root = dirname(path: __DIR__, levels: 2);

        $requiredDirectories = [
            $root . '/System/Flows/Login',
            $root . '/System/Flows/Logout',
            $root . '/System/Flows/Register',
            $root . '/System/Flows/ChangePassword',
            $root . '/System/Flows/ChangeEmail',
            $root . '/System/Flows/RecoverAccess',
            $root . '/System/Flows/VerifyIdentity',
            $root . '/System/Flows/CheckAuthentication',
            $root . '/System/Capabilities/Access/RequireAuthentication',
            $root . '/System/Capabilities/Access/RequirePermission',
            $root . '/System/Capabilities/Access/RequireRole',
            $root . '/System/Capabilities/Access/RiskBasedAccess',
            $root . '/System/Capabilities/Identity/Sessions/Runtime',
            $root . '/System/Capabilities/Identity/Tokens/Runtime',
            $root . '/System/Capabilities/Identity/Mfa/Runtime',
            $root . '/System/Capabilities/Identity/Passkey/Runtime',
            $root . '/System/Capabilities/ExternalIdentity/SingleSignOn/FederationRuntime',
            $root . '/System/Capabilities/ExternalIdentity/OAuth/Runtime',
            $root . '/System/Capabilities/ExternalIdentity/OpenIDConnect/Runtime',
            $root . '/System/Capabilities/IdentitySync/SCIM/Runtime',
            $root . '/System/Capabilities/Tenancy/Runtime',
            $root . '/System/Capabilities/Diagnostics',
        ];

        foreach ($requiredDirectories as $directory) {
            $this->assertDirectoryExists(
                directory: $directory,
                message  : "Canonical architectural boundary missing: {$directory}"
            );
        }
    }

    public function testCanonicalFlowsTopLevelShapeMatchesRefactorPlan() : void
    {
        $root     = dirname(path: __DIR__, levels: 2);
        $expected = [
            'ChangeEmail',
            'ChangePassword',
            'CheckAuthentication',
            'Login',
            'Logout',
            'RecoverAccess',
            'Register',
            'VerifyIdentity',
        ];

        $this->assertSame(
            expected: $expected,
            actual  : $this->topLevelDirectories(root: $root . '/System/Flows'),
            message : 'System/Flows top-level shape drifted away from the 8 canonical flow roots.'
        );
    }

    public function testCanonicalCapabilitiesTopLevelShapeMatchesRefactorPlan() : void
    {
        $root     = dirname(path: __DIR__, levels: 2);
        $expected = [
            'Access',
            'Diagnostics',
            'ExternalIdentity',
            'Identity',
            'IdentitySync',
            'Tenancy',
        ];

        $this->assertSame(
            expected: $expected,
            actual  : $this->topLevelDirectories(root: $root . '/System/Capabilities'),
            message : 'System/Capabilities top-level shape drifted away from the canonical owner zones.'
        );
    }

    public function testCanonicalAnchorFilesExist() : void
    {
        $root = dirname(path: __DIR__, levels: 2);

        $requiredFiles = [
            $root . '/System/Flows/Login/AuthenticationResult.php',
            $root . '/System/Flows/Register/RegistrationResult.php',
            $root . '/System/Flows/ChangeEmail/BeginEmailChange.php',
            $root . '/System/Flows/RecoverAccess/PasswordReset/BeginPasswordReset.php',
            $root . '/System/Flows/VerifyIdentity/EmailVerification/VerifyEmail.php',
            $root . '/System/Flows/CheckAuthentication/ReadCurrentUser/ReadCurrentUser.php',
            $root . '/System/Capabilities/Access/RequireAuthentication/RequireAuthentication.php',
            $root . '/System/Capabilities/Access/RequirePermission/RequirePermission.php',
            $root . '/System/Capabilities/Access/RequireRole/RequireRole.php',
            $root . '/System/Capabilities/Access/RiskBasedAccess/Runtime/AssessCurrentRisk/AssessCurrentRisk.php',
            $root . '/System/Capabilities/Identity/Sessions/Runtime/ReadActiveSessions/ReadActiveSessions.php',
            $root . '/System/Capabilities/Identity/Tokens/Runtime/Flow/RefreshAuthentication.php',
            $root . '/System/Capabilities/Identity/Mfa/Runtime/Enroll/StartMfaEnrollment.php',
            $root . '/System/Capabilities/Identity/Passkey/Runtime/ListPasskeys/ListPasskeys.php',
            $root . '/System/Capabilities/ExternalIdentity/SingleSignOn/FederationRuntime/StartFederatedLogin/StartFederatedLogin.php',
            $root . '/System/Capabilities/ExternalIdentity/OAuth/Runtime/AuthorizeCode/AuthorizeCode.php',
            $root . '/System/Capabilities/ExternalIdentity/OpenIDConnect/Runtime/ReadProviderMetadata/ReadOidcProviderMetadata.php',
            $root . '/System/Capabilities/IdentitySync/SCIM/Runtime/Bulk/RunScimBulk.php',
            $root . '/System/Capabilities/Tenancy/Runtime/Tenant/CreateTenant/CreateTenant.php',
            $root . '/System/Capabilities/Diagnostics/Diagnostics.php',
        ];

        foreach ($requiredFiles as $file) {
            $this->assertFileExists(
                filename: $file,
                message : "Canonical anchor file missing: {$file}"
            );
        }
    }

    /**
     * @throws ReflectionException
     */
    public function testZoneFacadesAreDecomposedIntoLocalOwnerFacades() : void
    {
        $root = dirname(path: __DIR__, levels: 2);

        $requiredFiles = [
            $root . '/System/Capabilities/Identity/IdentityOwners/Authentication.php',
            $root . '/System/Capabilities/Identity/IdentityOwners/Account.php',
            $root . '/System/Capabilities/Identity/IdentityOwners/Recovery.php',
            $root . '/System/Capabilities/Identity/IdentityOwners/Verification.php',
            $root . '/System/Capabilities/Identity/Sessions/Sessions.php',
            $root . '/System/Capabilities/Identity/Mfa/Mfa.php',
            $root . '/System/Capabilities/Identity/Passkey/Passkey.php',
            $root . '/System/Capabilities/ExternalIdentity/OAuth/OAuth.php',
            $root . '/System/Capabilities/ExternalIdentity/OpenIDConnect/OpenIDConnect.php',
            $root . '/System/Capabilities/ExternalIdentity/SingleSignOn/SingleSignOn.php',
            $root . '/System/Capabilities/IdentitySync/SCIM/SCIM.php',
            $root . '/System/Capabilities/IdentitySync/Provisioning/Provisioning.php',
            $root . '/System/Capabilities/Tenancy/Tenants/Tenants.php',
            $root . '/System/Capabilities/Tenancy/Security/Security.php',
            $root . '/System/Capabilities/Tenancy/Tenancy.php',
        ];

        foreach ($requiredFiles as $file) {
            $this->assertFileExists(
                filename: $file,
                message : "Local owner facade missing: {$file}"
            );
        }

        $expectedConstructorTypes = [
            Identity::class         => [
                Authentication::class,
                Sessions::class,
                Account::class,
                Recovery::class,
                Verification::class,
                Mfa::class,
                Passkey::class,
                SessionIdentityInterface::class,
                JwtIdentityInterface::class,
            ],
            ExternalIdentity::class => [
                OAuth::class,
                OpenIDConnect::class,
                SingleSignOn::class,
            ],
            IdentitySync::class     => [
                SCIM::class,
                Provisioning::class,
            ],
            Tenancy::class          => [
                Tenants::class,
                Security::class,
            ],
        ];

        foreach ($expectedConstructorTypes as $class => $expectedTypes) {
            $constructor = new ReflectionClass(objectOrClass: $class)->getConstructor();

            self::assertNotNull(actual: $constructor, message: "Facade constructor missing: {$class}");

            $actualTypes = array_map(
                callback: static function (ReflectionParameter $parameter) : string|null {
                    $type = $parameter->getType();

                    return $type instanceof ReflectionNamedType ? $type->getName() : null;
                },
                array   : $constructor->getParameters()
            );

            $this->assertSame(
                expected: $expectedTypes,
                actual  : $actualTypes,
                message : "Facade constructor drifted away from local-owner composition: {$class}"
            );
        }
    }

    public function testCanonicalRootUnitsDeclareCanonicalNamespaces() : void
    {
        $root = dirname(path: __DIR__, levels: 2);

        $expectedNamespaces = [
            $root . '/System/Auth.php'                            => 'namespace Avax\\Auth\\System;',
            $root . '/System/AuthInterface.php'                   => 'namespace Avax\\Auth\\System;',
            $root . '/System/Configuration/AuthBuilder.php'       => 'namespace Avax\\Auth\\System\\Configuration;',
            $root . '/System/Foundation/Clock.php'                => 'namespace Avax\\Auth\\System\\Foundation;',
            $root . '/System/Foundation/IdGenerator.php'          => 'namespace Avax\\Auth\\System\\Foundation;',
            $root . '/System/Foundation/IdGeneratorInterface.php' => 'namespace Avax\\Auth\\System\\Foundation;',
        ];

        foreach ($expectedNamespaces as $file => $namespace) {
            $contents = file_get_contents(filename: $file);

            self::assertIsString(actual: $contents);
            $declaredNamespace = preg_match(pattern: '/^namespace\s+([^;]+);$/m', subject: $contents, matches: $matches) === 1
                ? 'namespace ' . $matches[1] . ';'
                : null;

            $this->assertSame(
                expected: $namespace,
                actual  : $declaredNamespace,
                message : "Canonical root unit must declare canonical namespace: {$file}"
            );
        }
    }

    public function testFoundationSubtreeUnitsDeclareCanonicalNamespaces() : void
    {
        $root = dirname(path: __DIR__, levels: 2);

        $expectedNamespaces = [
            $root . '/System/Foundation/Exceptions/AuthException.php'             => 'namespace Avax\\Auth\\System\\Foundation\\Exceptions;',
            $root . '/System/Foundation/Exceptions/ConfigurationException.php'    => 'namespace Avax\\Auth\\System\\Foundation\\Exceptions;',
            $root . '/System/Foundation/Exceptions/ExternalIdentityException.php' => 'namespace Avax\\Auth\\System\\Foundation\\Exceptions;',
            $root . '/System/Foundation/Exceptions/IdentitySyncException.php'     => 'namespace Avax\\Auth\\System\\Foundation\\Exceptions;',
            $root . '/System/Foundation/Ids/ChallengeId.php'                      => 'namespace Avax\\Auth\\System\\Foundation\\Ids;',
            $root . '/System/Foundation/Ids/ClientId.php'                         => 'namespace Avax\\Auth\\System\\Foundation\\Ids;',
            $root . '/System/Foundation/Ids/DirectoryId.php'                      => 'namespace Avax\\Auth\\System\\Foundation\\Ids;',
            $root . '/System/Foundation/Ids/PasskeyId.php'                        => 'namespace Avax\\Auth\\System\\Foundation\\Ids;',
            $root . '/System/Foundation/Ids/SessionId.php'                        => 'namespace Avax\\Auth\\System\\Foundation\\Ids;',
            $root . '/System/Foundation/Ids/TenantId.php'                         => 'namespace Avax\\Auth\\System\\Foundation\\Ids;',
            $root . '/System/Foundation/Ids/TokenId.php'                          => 'namespace Avax\\Auth\\System\\Foundation\\Ids;',
            $root . '/System/Foundation/Ids/UserId.php'                           => 'namespace Avax\\Auth\\System\\Foundation\\Ids;',
            $root . '/System/Foundation/Text/MaskSecret.php'                      => 'namespace Avax\\Auth\\System\\Foundation\\Text;',
            $root . '/System/Foundation/Text/NormalizeEmail.php'                  => 'namespace Avax\\Auth\\System\\Foundation\\Text;',
            $root . '/System/Foundation/Text/NormalizeScope.php'                  => 'namespace Avax\\Auth\\System\\Foundation\\Text;',
            $root . '/System/Foundation/Text/NormalizeUsername.php'               => 'namespace Avax\\Auth\\System\\Foundation\\Text;',
            $root . '/System/Foundation/Time/Clock.php'                           => 'namespace Avax\\Auth\\System\\Foundation\\Time;',
            $root . '/System/Foundation/Time/Expiry.php'                          => 'namespace Avax\\Auth\\System\\Foundation\\Time;',
            $root . '/System/Foundation/Time/SystemClock.php'                     => 'namespace Avax\\Auth\\System\\Foundation\\Time;',
            $root . '/System/Foundation/Time/TimeWindow.php'                      => 'namespace Avax\\Auth\\System\\Foundation\\Time;',
        ];

        foreach ($expectedNamespaces as $file => $namespace) {
            $contents = file_get_contents(filename: $file);

            self::assertIsString(actual: $contents);
            $declaredNamespace = preg_match(pattern: '/^namespace\s+([^;]+);$/m', subject: $contents, matches: $matches) === 1
                ? 'namespace ' . $matches[1] . ';'
                : null;

            $this->assertSame(
                expected: $namespace,
                actual  : $declaredNamespace,
                message : "Foundation subtree unit must declare canonical namespace: {$file}"
            );
        }
    }

    public function testCanonicalContractsUsePluralSystemRoots() : void
    {
        $root  = dirname(path: __DIR__, levels: 2);
        $files = [
            $root . '/AGENTS.md',
            $root . '/README.md',
            $root . '/docs/STATUS.md',
            $root . '/docs/architecture/system-shape.md',
            $root . '/docs/architecture/flow-boundaries.md',
            $root . '/docs/architecture/capability-boundaries.md',
            $root . '/docs/architecture/migration-map.md',
        ];

        foreach (glob(pattern: $root . '/docs/flows/*.md') ?: [] as $flowDoc) {
            $files[] = $flowDoc;
        }

        foreach ($files as $file) {
            $contents = file_get_contents(filename: $file);

            self::assertIsString(actual: $contents);
            $this->assertStringNotContainsString(
                needle  : 'System/Flow/',
                haystack: $contents,
                message : "Legacy singular flow root documented in {$file}"
            );
            $this->assertStringNotContainsString(
                needle  : 'System/Capability/',
                haystack: $contents,
                message : "Legacy singular capability root documented in {$file}"
            );
        }

        $agents = file_get_contents(filename: $root . '/AGENTS.md');
        $shape  = file_get_contents(filename: $root . '/docs/architecture/system-shape.md');

        self::assertIsString(actual: $agents);
        self::assertIsString(actual: $shape);
        $this->assertStringContainsString(needle: 'Flows/', haystack: $agents);
        $this->assertStringContainsString(needle: 'Capabilities/', haystack: $agents);
        $this->assertStringContainsString(needle: 'System/Flows/', haystack: $shape);
        $this->assertStringContainsString(needle: 'System/Capabilities/', haystack: $shape);
    }

    public function testLegacySingularDirectoryRootsAreRemoved() : void
    {
        $root       = dirname(path: __DIR__, levels: 2);
        $legacyDirs = [
            $root . '/System/Flow',
            $root . '/System/Capability',
            $root . '/tests/Flow',
            $root . '/tests/Capability',
        ];

        foreach ($legacyDirs as $directory) {
            $this->assertDirectoryDoesNotExist(
                directory: $directory,
                message  : "Legacy singular directory root still present: {$directory}"
            );
        }
    }

    public function testNoLegacyCompatibilityShimsRemainInSystemTree() : void
    {
        $root     = dirname(path: __DIR__, levels: 2);
        $iterator = new RecursiveIteratorIterator(
            iterator: new RecursiveDirectoryIterator(directory: $root . '/System', flags: FilesystemIterator::SKIP_DOTS)
        );

        /** @var SplFileInfo $file */
        foreach ($iterator as $file) {
            if (! $file->isFile() || $file->getExtension() !== 'php') {
                continue;
            }

            $contents = file_get_contents(filename: $file->getPathname());

            self::assertIsString(actual: $contents);
            $this->assertStringNotContainsString(
                needle  : 'class_alias(',
                haystack: $contents,
                message : "Legacy class_alias shim still exists in {$file->getPathname()}"
            );
            $this->assertStringNotContainsString(
                needle  : 'namespace Avax\\Auth\\System\\Capability\\',
                haystack: $contents,
                message : "Legacy singular capability namespace still exists in {$file->getPathname()}"
            );
            $this->assertStringNotContainsString(
                needle  : 'namespace Avax\\Auth\\System\\Flow\\',
                haystack: $contents,
                message : "Legacy singular flow namespace still exists in {$file->getPathname()}"
            );
        }
    }

    public function testMigratedNamespaceRootsAreFullyCutOverInSourceAndTests() : void
    {
        $root        = dirname(path: __DIR__, levels: 2);
        $bannedRoots = [
            'Avax\\Auth\\System\\Capabilities\\AdminRealm\\',
            'Avax\\Auth\\System\\Capabilities\\Explainability\\',
            'Avax\\Auth\\System\\Capabilities\\Federation\\',
            'Avax\\Auth\\System\\Capabilities\\Lifecycle\\',
            'Avax\\Auth\\System\\Capabilities\\OAuth\\',
            'Avax\\Auth\\System\\Capabilities\\Oidc\\',
            'Avax\\Auth\\System\\Capabilities\\Passkey\\',
            'Avax\\Auth\\System\\Capabilities\\PasswordHashing\\',
            'Avax\\Auth\\System\\Capabilities\\Risk\\',
            'Avax\\Auth\\System\\Capabilities\\Scim\\',
            'Avax\\Auth\\System\\Capabilities\\Session\\',
            'Avax\\Auth\\System\\Capabilities\\Tenant\\',
            'Avax\\Auth\\System\\Capabilities\\TenantSecurity\\',
            'Avax\\Auth\\System\\Capabilities\\Throttle\\',
            'Avax\\Auth\\System\\Capabilities\\User\\',
            'Avax\\Auth\\System\\Capabilities\\UserSource\\',
            'Avax\\Auth\\System\\Flows\\AdminRealm\\',
            'Avax\\Auth\\System\\Flows\\AuthenticateRequest\\',
            'Avax\\Auth\\System\\Flows\\Diagnostics\\',
            'Avax\\Auth\\System\\Flows\\Federation\\',
            'Avax\\Auth\\System\\Flows\\Mfa\\',
            'Avax\\Auth\\System\\Flows\\OAuth\\',
            'Avax\\Auth\\System\\Flows\\Oidc\\',
            'Avax\\Auth\\System\\Flows\\Passkey\\',
            'Avax\\Auth\\System\\Flows\\Provisioning\\',
            'Avax\\Auth\\System\\Flows\\ReadCurrentUser\\',
            'Avax\\Auth\\System\\Flows\\Recover\\',
            'Avax\\Auth\\System\\Flows\\Risk\\',
            'Avax\\Auth\\System\\Flows\\Scim\\',
            'Avax\\Auth\\System\\Flows\\Session\\',
            'Avax\\Auth\\System\\Flows\\Tenant\\',
            'Avax\\Auth\\System\\Flows\\TenantSecurity\\',
            'Avax\\Auth\\System\\Flows\\Token\\',
            'Avax\\Auth\\System\\Flows\\Verify\\',
        ];
        $directories = [
            $root . '/System',
            $root . '/tests',
            $root . '/integrations',
        ];

        foreach ($directories as $directory) {
            $iterator = new RecursiveIteratorIterator(
                iterator: new RecursiveDirectoryIterator(directory: $directory, flags: FilesystemIterator::SKIP_DOTS)
            );

            /** @var SplFileInfo $file */
            foreach ($iterator as $file) {
                if (! $file->isFile() || $file->getExtension() !== 'php') {
                    continue;
                }

                $contents = file_get_contents(filename: $file->getPathname());

                self::assertIsString(actual: $contents);

                foreach ($bannedRoots as $rootPrefix) {
                    $this->assertStringNotContainsString(
                        needle  : $rootPrefix,
                        haystack: $contents,
                        message : "Stale migrated namespace root still referenced in {$file->getPathname()}: {$rootPrefix}"
                    );
                }
            }
        }
    }

    public function testRefactorAndClosureDocsReflectPluralCanonicalRoots() : void
    {
        $root  = dirname(path: __DIR__, levels: 2);
        $files = [
            $root . '/REFAKTOR.md',
            $root . '/complete-this.md',
        ];

        foreach ($files as $file) {
            $contents = file_get_contents(filename: $file);

            self::assertIsString(actual: $contents);
            $this->assertStringNotContainsString(
                needle  : 'System/Flow/',
                haystack: $contents,
                message : "Legacy singular flow root still documented in {$file}"
            );
            $this->assertStringNotContainsString(
                needle  : 'System/Capability/',
                haystack: $contents,
                message : "Legacy singular capability root still documented in {$file}"
            );
        }
    }

    public function testMutationConfigurationUsesPluralSystemRoots() : void
    {
        $root     = dirname(path: __DIR__, levels: 2);
        $contents = file_get_contents(filename: $root . '/infection.json.dist');

        self::assertIsString(actual: $contents);
        $this->assertStringNotContainsString(needle: 'System/Flow/', haystack: $contents);
        $this->assertStringNotContainsString(needle: 'System/Capability/', haystack: $contents);
        $this->assertStringContainsString(needle: 'System/Flows/', haystack: $contents);
        $this->assertStringContainsString(needle: 'System/Capabilities/', haystack: $contents);
    }

    public function testTargetFoundationArchitectureExists() : void
    {
        $root = dirname(path: __DIR__, levels: 2);

        $requiredDirectories = [
            $root . '/System/Foundation/Ids',
            $root . '/System/Foundation/Time',
            $root . '/System/Foundation/Text',
            $root . '/System/Foundation/Exceptions',
        ];

        foreach ($requiredDirectories as $directory) {
            $this->assertDirectoryExists(
                directory: $directory,
                message  : "Target foundation directory missing: {$directory}"
            );
        }

        $requiredFiles = [
            $root . '/System/Foundation/Ids/UserId.php',
            $root . '/System/Foundation/Ids/TenantId.php',
            $root . '/System/Foundation/Ids/SessionId.php',
            $root . '/System/Foundation/Ids/TokenId.php',
            $root . '/System/Foundation/Ids/ClientId.php',
            $root . '/System/Foundation/Ids/DirectoryId.php',
            $root . '/System/Foundation/Ids/PasskeyId.php',
            $root . '/System/Foundation/Ids/ChallengeId.php',
            $root . '/System/Foundation/Time/Clock.php',
            $root . '/System/Foundation/Time/SystemClock.php',
            $root . '/System/Foundation/Time/Expiry.php',
            $root . '/System/Foundation/Time/TimeWindow.php',
            $root . '/System/Foundation/Text/NormalizeEmail.php',
            $root . '/System/Foundation/Text/NormalizeUsername.php',
            $root . '/System/Foundation/Text/NormalizeScope.php',
            $root . '/System/Foundation/Text/MaskSecret.php',
            $root . '/System/Foundation/Exceptions/AuthException.php',
            $root . '/System/Foundation/Exceptions/ConfigurationException.php',
            $root . '/System/Foundation/Exceptions/ExternalIdentityException.php',
            $root . '/System/Foundation/Exceptions/IdentitySyncException.php',
        ];

        foreach ($requiredFiles as $file) {
            $this->assertFileExists(
                filename: $file,
                message : "Target foundation file missing: {$file}"
            );
        }
    }

    public function testTargetCapabilitiesArchitectureExists() : void
    {
        $root = dirname(path: __DIR__, levels: 2);

        $requiredDirectories = [
            $root . '/System/Capabilities/Access/Authentication',
            $root . '/System/Capabilities/Access/RequireAuthentication',
            $root . '/System/Capabilities/Access/RequirePermission',
            $root . '/System/Capabilities/Access/RequireRole',
            $root . '/System/Capabilities/Access/RiskBasedAccess/Runtime',
            $root . '/System/Capabilities/Identity/Sessions/Runtime',
            $root . '/System/Capabilities/Identity/Tokens/Runtime',
            $root . '/System/Capabilities/Identity/Mfa/Runtime',
            $root . '/System/Capabilities/Identity/Passkey/Runtime',
            $root . '/System/Capabilities/ExternalIdentity/SingleSignOn/FederationRuntime',
            $root . '/System/Capabilities/ExternalIdentity/OAuth/Runtime',
            $root . '/System/Capabilities/ExternalIdentity/OpenIDConnect/Runtime',
            $root . '/System/Capabilities/IdentitySync/SCIM/Runtime',
            $root . '/System/Capabilities/Tenancy/Runtime',
            $root . '/System/Capabilities/Diagnostics',
        ];

        foreach ($requiredDirectories as $directory) {
            $this->assertDirectoryExists(
                directory: $directory,
                message  : "Target capabilities directory missing: {$directory}"
            );
        }

        $requiredFiles = [
            $root . '/System/Capabilities/Access/RequireAuthentication/RequireAuthentication.php',
            $root . '/System/Capabilities/Access/RequireAuthentication/Unauthenticated.php',
            $root . '/System/Capabilities/Access/RequirePermission/RequirePermission.php',
            $root . '/System/Capabilities/Access/RequirePermission/PermissionDenied.php',
            $root . '/System/Capabilities/Access/RequireRole/RequireRole.php',
            $root . '/System/Capabilities/Access/RequireRole/RoleDenied.php',
            $root . '/System/Capabilities/Access/RiskBasedAccess/Runtime/AssessCurrentRisk/AssessCurrentRisk.php',
            $root . '/System/Capabilities/Access/RiskBasedAccess/Runtime/ReadRiskSignals/ReadRiskSignals.php',
            $root . '/System/Capabilities/Identity/Sessions/Runtime/ReadActiveSessions/ReadActiveSessions.php',
            $root . '/System/Capabilities/Identity/Sessions/Runtime/RevokeSession/RevokeSession.php',
            $root . '/System/Capabilities/Identity/Sessions/Runtime/LogoutAllSessions/LogoutAllSessions.php',
            $root . '/System/Capabilities/Identity/Tokens/TokenState.php',
            $root . '/System/Capabilities/Identity/Tokens/ReadToken.php',
            $root . '/System/Capabilities/Identity/Tokens/Runtime/Flow/RefreshAuthentication.php',
            $root . '/System/Capabilities/Identity/Tokens/Runtime/Record/IssuedToken.php',
            $root . '/System/Capabilities/Identity/Mfa/Runtime/Enroll/StartMfaEnrollment.php',
            $root . '/System/Capabilities/Identity/Mfa/Runtime/Enroll/ConfirmMfaEnrollment.php',
            $root . '/System/Capabilities/Identity/Mfa/Runtime/Verify/StartMfaChallenge.php',
            $root . '/System/Capabilities/Identity/Mfa/Runtime/Verify/VerifyMfaChallenge.php',
            $root . '/System/Capabilities/Identity/Mfa/Runtime/Recover/StartMfaRecovery.php',
            $root . '/System/Capabilities/Identity/Mfa/Runtime/Recover/ConfirmMfaRecovery.php',
            $root . '/System/Capabilities/Identity/Mfa/Runtime/Backup/RegenerateBackupCodes.php',
            $root . '/System/Capabilities/Identity/Mfa/Runtime/Disable/DisableMfa.php',
            $root . '/System/Capabilities/Identity/Passkey/Runtime/BeginRegistration/BeginPasskeyRegistration.php',
            $root . '/System/Capabilities/Identity/Passkey/Runtime/CompleteRegistration/CompletePasskeyRegistration.php',
            $root . '/System/Capabilities/Identity/Passkey/Runtime/BeginAuthentication/BeginPasskeyAuthentication.php',
            $root . '/System/Capabilities/Identity/Passkey/Runtime/CompleteAuthentication/CompletePasskeyAuthentication.php',
            $root . '/System/Capabilities/Identity/Passkey/Runtime/ListPasskeys/ListPasskeys.php',
            $root . '/System/Capabilities/Identity/Passkey/Runtime/RevokePasskey/RevokePasskey.php',
            $root . '/System/Capabilities/ExternalIdentity/SingleSignOn/FederationRuntime/StartFederatedLogin/StartFederatedLogin.php',
            $root . '/System/Capabilities/ExternalIdentity/SingleSignOn/FederationRuntime/CompleteFederatedLogin/CompleteFederatedLogin.php',
            $root . '/System/Capabilities/ExternalIdentity/OAuth/Runtime/AuthorizeCode/AuthorizeCode.php',
            $root . '/System/Capabilities/ExternalIdentity/OAuth/Runtime/ExchangeAuthorizationCode/ExchangeAuthorizationCode.php',
            $root . '/System/Capabilities/ExternalIdentity/OAuth/Runtime/ExchangeClientCredentials/ExchangeClientCredentials.php',
            $root . '/System/Capabilities/ExternalIdentity/OAuth/Runtime/ExchangeRefreshToken/ExchangeRefreshToken.php',
            $root . '/System/Capabilities/ExternalIdentity/OAuth/Runtime/ReadClients/ReadClients.php',
            $root . '/System/Capabilities/ExternalIdentity/OAuth/Runtime/ReadWorkloadIdentities/ReadWorkloadIdentities.php',
            $root . '/System/Capabilities/ExternalIdentity/OAuth/Runtime/RegisterClient/RegisterClient.php',
            $root . '/System/Capabilities/ExternalIdentity/OAuth/Runtime/UpdateClient/UpdateClient.php',
            $root . '/System/Capabilities/ExternalIdentity/OpenIDConnect/Runtime/ReadProviderMetadata/ReadOidcProviderMetadata.php',
            $root . '/System/Capabilities/ExternalIdentity/OpenIDConnect/Runtime/ReadJsonWebKeySet/ReadOidcJsonWebKeySet.php',
            $root . '/System/Capabilities/ExternalIdentity/OpenIDConnect/Runtime/PushAuthorizationRequest/PushAuthorizationRequest.php',
            $root . '/System/Capabilities/ExternalIdentity/OpenIDConnect/Runtime/ValidateRequestObject/ValidateRequestObject.php',
            $root . '/System/Capabilities/ExternalIdentity/OpenIDConnect/Runtime/Logout/Logout.php',
            $root . '/System/Capabilities/ExternalIdentity/OpenIDConnect/Runtime/ReadUserInfo/ReadOidcUserInfo.php',
            $root . '/System/Capabilities/IdentitySync/SCIM/Runtime/Bulk/RunScimBulk.php',
            $root . '/System/Capabilities/IdentitySync/SCIM/Runtime/ReadDirectories/ReadScimDirectories.php',
            $root . '/System/Capabilities/IdentitySync/SCIM/Runtime/ReadUsers/ReadScimUsers.php',
            $root . '/System/Capabilities/IdentitySync/SCIM/Runtime/ReadGroups/ReadScimGroups.php',
            $root . '/System/Capabilities/IdentitySync/SCIM/Runtime/MarkOutage/MarkScimDirectoryOutage.php',
            $root . '/System/Capabilities/IdentitySync/SCIM/Runtime/RecoverOutage/RecoverScimDirectoryOutage.php',
            $root . '/System/Capabilities/IdentitySync/SCIM/Runtime/RegisterDirectory/RegisterScimDirectory.php',
            $root . '/System/Capabilities/IdentitySync/SCIM/Runtime/RotateToken/RotateScimToken.php',
            $root . '/System/Capabilities/IdentitySync/SCIM/Runtime/DeleteUser/DeleteScimUser.php',
            $root . '/System/Capabilities/IdentitySync/SCIM/Runtime/ProvisionUser/ProvisionScimUser.php',
            $root . '/System/Capabilities/Tenancy/Runtime/Tenant/CreateTenant/CreateTenant.php',
            $root . '/System/Capabilities/Tenancy/Runtime/Tenant/ReadTenants/ReadTenants.php',
            $root . '/System/Capabilities/Tenancy/Runtime/Tenant/ReadMembers/ReadTenantMembers.php',
            $root . '/System/Capabilities/Tenancy/Runtime/Tenant/InviteMember/InviteTenantMember.php',
            $root . '/System/Capabilities/Tenancy/Runtime/Tenant/AcceptInvite/AcceptTenantInvite.php',
            $root . '/System/Capabilities/Tenancy/Runtime/Tenant/RemoveMember/RemoveTenantMember.php',
            $root . '/System/Capabilities/Tenancy/Runtime/Tenant/SuspendMember/SuspendTenantMember.php',
            $root . '/System/Capabilities/Tenancy/Runtime/Tenant/TransferOwnership/TransferTenantOwnership.php',
            $root . '/System/Capabilities/Tenancy/Runtime/TenantSecurity/ReadConfiguration/ReadTenantSecurityConfiguration.php',
            $root . '/System/Capabilities/Tenancy/Runtime/TenantSecurity/ReadChangeRequests/ReadTenantSecurityChangeRequests.php',
            $root . '/System/Capabilities/Tenancy/Runtime/TenantSecurity/BeginChange/BeginTenantSecurityChange.php',
            $root . '/System/Capabilities/Tenancy/Runtime/TenantSecurity/ApplyChange/ApplyTenantSecurityChange.php',
            $root . '/System/Capabilities/Tenancy/Runtime/TenantSecurity/ApproveChange/ApproveTenantSecurityChange.php',
            $root . '/System/Capabilities/Tenancy/Runtime/TenantSecurity/RollbackChange/RollbackTenantSecurityChange.php',
            $root . '/System/Capabilities/Diagnostics/Diagnostics.php',
            $root . '/System/Capabilities/Diagnostics/DiagnosticReport.php',
        ];

        foreach ($requiredFiles as $file) {
            $this->assertFileExists(
                filename: $file,
                message : "Target capabilities file missing: {$file}"
            );
        }

        return;
    }

    public function testTargetTestArchitectureExists() : void
    {
        $root = dirname(path: __DIR__, levels: 2);

        $requiredDirectories = [
            $root . '/tests/Unit/System/Flows/Login',
            $root . '/tests/Unit/System/Flows/Logout',
            $root . '/tests/Unit/System/Flows/Register',
            $root . '/tests/Unit/System/Flows/ChangePassword',
            $root . '/tests/Unit/System/Flows/ChangeEmail',
            $root . '/tests/Unit/System/Flows/RecoverAccess',
            $root . '/tests/Unit/System/Flows/VerifyIdentity',
            $root . '/tests/Unit/System/Flows/CheckAuthentication',
            $root . '/tests/Unit/System/Capabilities/Access/Authentication',
            $root . '/tests/Unit/System/Capabilities/Access/Authorization',
            $root . '/tests/Unit/System/Capabilities/Access/RiskBasedAccess',
            $root . '/tests/Unit/System/Capabilities/Identity/Sessions',
            $root . '/tests/Unit/System/Capabilities/Identity/Tokens',
            $root . '/tests/Unit/System/Capabilities/Identity/Mfa',
            $root . '/tests/Unit/System/Capabilities/Identity/Passkey',
            $root . '/tests/Unit/System/Capabilities/ExternalIdentity/SingleSignOn',
            $root . '/tests/Unit/System/Capabilities/ExternalIdentity/OAuth',
            $root . '/tests/Unit/System/Capabilities/ExternalIdentity/OpenIDConnect',
            $root . '/tests/Unit/System/Capabilities/IdentitySync/SCIM',
            $root . '/tests/Unit/System/Capabilities/Tenancy',
            $root . '/tests/Unit/System/Capabilities/Diagnostics',
            $root . '/tests/Unit/System/Configuration',
            $root . '/tests/Unit/System/Foundation/Ids',
            $root . '/tests/Unit/System/Foundation/Time',
            $root . '/tests/Unit/System/Foundation/Text',
            $root . '/tests/Integration',
            $root . '/tests/Characterization',
        ];

        foreach ($requiredDirectories as $directory) {
            $this->assertDirectoryExists(
                directory: $directory,
                message  : "Target tests directory missing: {$directory}"
            );
        }

        $requiredFiles = [
            $root . '/tests/Unit/System/Flows/Login/LoginTest.php',
            $root . '/tests/Unit/System/Flows/Login/CredentialsTest.php',
            $root . '/tests/Unit/System/Flows/Logout/LogoutTest.php',
            $root . '/tests/Unit/System/Flows/Register/RegisterTest.php',
            $root . '/tests/Unit/System/Flows/Register/RegistrationDataTest.php',
            $root . '/tests/Unit/System/Flows/ChangePassword/ChangePasswordTest.php',
            $root . '/tests/Unit/System/Flows/ChangeEmail/ChangeEmailTest.php',
            $root . '/tests/Unit/System/Flows/RecoverAccess/BeginRecoveryTest.php',
            $root . '/tests/Unit/System/Flows/RecoverAccess/ConfirmRecoveryTest.php',
            $root . '/tests/Unit/System/Flows/VerifyIdentity/VerifyIdentityTest.php',
            $root . '/tests/Unit/System/Flows/CheckAuthentication/CheckAuthenticationTest.php',
            $root . '/tests/Unit/System/Flows/CheckAuthentication/ReadAuthenticatedUserTest.php',
            $root . '/tests/Unit/System/Capabilities/Access/Authentication/AuthenticateRequestTest.php',
            $root . '/tests/Unit/System/Capabilities/Access/Authentication/RequireAuthenticationTest.php',
            $root . '/tests/Unit/System/Capabilities/Access/Authorization/RequirePermissionTest.php',
            $root . '/tests/Unit/System/Capabilities/Access/Authorization/RequireRoleTest.php',
            $root . '/tests/Unit/System/Capabilities/Access/RiskBasedAccess/AssessAccessRiskTest.php',
            $root . '/tests/Unit/System/Capabilities/Access/RiskBasedAccess/AccessRiskPolicyTest.php',
            $root . '/tests/Unit/System/Capabilities/Identity/Sessions/ReadActiveSessionsTest.php',
            $root . '/tests/Unit/System/Capabilities/Identity/Sessions/RevokeSessionTest.php',
            $root . '/tests/Unit/System/Capabilities/Identity/Sessions/LogoutAllSessionsTest.php',
            $root . '/tests/Unit/System/Capabilities/Identity/Tokens/IssueTokenTest.php',
            $root . '/tests/Unit/System/Capabilities/Identity/Tokens/RefreshTokenTest.php',
            $root . '/tests/Unit/System/Capabilities/Identity/Tokens/RevokeTokenTest.php',
            $root . '/tests/Unit/System/Capabilities/Identity/Mfa/StartEnrollmentTest.php',
            $root . '/tests/Unit/System/Capabilities/Identity/Mfa/ConfirmEnrollmentTest.php',
            $root . '/tests/Unit/System/Capabilities/Identity/Mfa/StartChallengeTest.php',
            $root . '/tests/Unit/System/Capabilities/Identity/Mfa/VerifyChallengeTest.php',
            $root . '/tests/Unit/System/Capabilities/Identity/Mfa/RegenerateBackupCodesTest.php',
            $root . '/tests/Unit/System/Capabilities/Identity/Mfa/DisableMfaTest.php',
            $root . '/tests/Unit/System/Capabilities/Identity/Passkey/StartRegistrationTest.php',
            $root . '/tests/Unit/System/Capabilities/Identity/Passkey/ConfirmRegistrationTest.php',
            $root . '/tests/Unit/System/Capabilities/Identity/Passkey/StartChallengeTest.php',
            $root . '/tests/Unit/System/Capabilities/Identity/Passkey/VerifyChallengeTest.php',
            $root . '/tests/Unit/System/Capabilities/Identity/Passkey/RemovePasskeyTest.php',
            $root . '/tests/Unit/System/Capabilities/ExternalIdentity/SingleSignOn/StartSingleSignOnTest.php',
            $root . '/tests/Unit/System/Capabilities/ExternalIdentity/SingleSignOn/CompleteSingleSignOnTest.php',
            $root . '/tests/Unit/System/Capabilities/ExternalIdentity/OAuth/AuthorizeCodeTest.php',
            $root . '/tests/Unit/System/Capabilities/ExternalIdentity/OAuth/ExchangeAuthorizationCodeTest.php',
            $root . '/tests/Unit/System/Capabilities/ExternalIdentity/OAuth/ExchangeClientCredentialsTest.php',
            $root . '/tests/Unit/System/Capabilities/ExternalIdentity/OAuth/ExchangeRefreshTokenTest.php',
            $root . '/tests/Unit/System/Capabilities/ExternalIdentity/OAuth/IntrospectTokenTest.php',
            $root . '/tests/Unit/System/Capabilities/ExternalIdentity/OpenIDConnect/ReadProviderMetadataTest.php',
            $root . '/tests/Unit/System/Capabilities/ExternalIdentity/OpenIDConnect/ReadJsonWebKeySetTest.php',
            $root . '/tests/Unit/System/Capabilities/ExternalIdentity/OpenIDConnect/PushAuthorizationRequestTest.php',
            $root . '/tests/Unit/System/Capabilities/ExternalIdentity/OpenIDConnect/ValidateRequestObjectTest.php',
            $root . '/tests/Unit/System/Capabilities/ExternalIdentity/OpenIDConnect/ReadUserInfoTest.php',
            $root . '/tests/Unit/System/Capabilities/IdentitySync/AddIdentityTest.php',
            $root . '/tests/Unit/System/Capabilities/IdentitySync/RemoveIdentityTest.php',
            $root . '/tests/Unit/System/Capabilities/IdentitySync/SCIM/RegisterDirectoryTest.php',
            $root . '/tests/Unit/System/Capabilities/IdentitySync/SCIM/RotateDirectoryTokenTest.php',
            $root . '/tests/Unit/System/Capabilities/IdentitySync/SCIM/AddUserTest.php',
            $root . '/tests/Unit/System/Capabilities/IdentitySync/SCIM/RemoveUserTest.php',
            $root . '/tests/Unit/System/Capabilities/IdentitySync/SCIM/SyncGroupsTest.php',
            $root . '/tests/Unit/System/Capabilities/IdentitySync/SCIM/RunBulkSyncTest.php',
            $root . '/tests/Unit/System/Capabilities/Tenancy/CreateTenantTest.php',
            $root . '/tests/Unit/System/Capabilities/Tenancy/InviteMemberTest.php',
            $root . '/tests/Unit/System/Capabilities/Tenancy/AcceptInviteTest.php',
            $root . '/tests/Unit/System/Capabilities/Tenancy/ReadMembersTest.php',
            $root . '/tests/Unit/System/Capabilities/Tenancy/SuspendMemberTest.php',
            $root . '/tests/Unit/System/Capabilities/Tenancy/TransferOwnershipTest.php',
            $root . '/tests/Unit/System/Capabilities/Diagnostics/RunDiagnosticsTest.php',
            $root . '/tests/Unit/System/Configuration/AuthBuilderTest.php',
            $root . '/tests/Unit/System/Configuration/AuthConfigurationTest.php',
            $root . '/tests/Unit/System/Foundation/Ids/UserIdTest.php',
            $root . '/tests/Unit/System/Foundation/Ids/TenantIdTest.php',
            $root . '/tests/Unit/System/Foundation/Time/ExpiryTest.php',
            $root . '/tests/Unit/System/Foundation/Time/TimeWindowTest.php',
            $root . '/tests/Unit/System/Foundation/Text/NormalizeEmailTest.php',
            $root . '/tests/Unit/System/Foundation/Text/NormalizeScopeTest.php',
            $root . '/tests/Integration/LoginFlowTest.php',
            $root . '/tests/Integration/RegisterFlowTest.php',
            $root . '/tests/Integration/ChangePasswordFlowTest.php',
            $root . '/tests/Integration/ChangeEmailFlowTest.php',
            $root . '/tests/Integration/RecoverAccessFlowTest.php',
            $root . '/tests/Integration/VerifyIdentityFlowTest.php',
            $root . '/tests/Integration/SessionLifecycleFlowTest.php',
            $root . '/tests/Integration/TokenLifecycleFlowTest.php',
            $root . '/tests/Integration/MfaFlowTest.php',
            $root . '/tests/Integration/PasskeyFlowTest.php',
            $root . '/tests/Integration/SingleSignOnFlowTest.php',
            $root . '/tests/Integration/ScimIdentitySyncFlowTest.php',
            $root . '/tests/Integration/TenantMembershipFlowTest.php',
            $root . '/tests/Characterization/LegacyAuthenticationBehaviorTest.php',
            $root . '/tests/Characterization/LegacySessionBehaviorTest.php',
            $root . '/tests/Characterization/LegacyTokenRefreshBehaviorTest.php',
            $root . '/tests/Characterization/LegacySingleSignOnBehaviorTest.php',
        ];

        foreach ($requiredFiles as $file) {
            $this->assertFileExists(
                filename: $file,
                message : "Target tests file missing: {$file}"
            );
        }
    }

    public function testCoreFlowsDoNotCreateClockInternally() : void
    {
        $root  = dirname(path: __DIR__, levels: 2);
        $files = [
            $root . '/System/Flows/Login/Login.php',
            $root . '/System/Flows/Register/Register.php',
            $root . '/System/Flows/CheckAuthentication/AuthenticateRequest/AuthenticateRequest.php',
        ];

        foreach ($files as $file) {
            $contents = file_get_contents(filename: $file);

            self::assertIsString(actual: $contents);
            $this->assertStringNotContainsString(
                needle  : 'new Clock(',
                haystack: $contents,
                message : "Flow must receive time through DI instead of creating Clock internally: {$file}"
            );
        }
    }

    /**
     * @return list<string>
     */
    private function topLevelDirectories(string $root) : array
    {
        $entries = scandir(directory: $root);
        self::assertNotFalse($entries);

        $directories = [];

        foreach ($entries as $entry) {
            if ($entry === '.' || $entry === '..') {
                continue;
            }

            if (is_dir(filename: $root . '/' . $entry)) {
                $directories[] = $entry;
            }
        }

        sort(array: $directories);

        return $directories;
    }
}
