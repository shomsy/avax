<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Configuration;

use Avax\Components\Identity\Capabilities\Accounts\InMemoryUserAccountDirectory;
use Avax\Components\Identity\Capabilities\Audit\NullIdentityAuditTrail;
use Avax\Components\Identity\Capabilities\ExternalIdentities\InMemoryExternalIdentityDirectory;
use Avax\Components\Identity\Capabilities\Passwords\InMemoryPasswordCredentialDirectory;
use Avax\Components\Identity\Capabilities\Passwords\NativePasswordHashing;
use Avax\Components\Identity\Capabilities\Permissions\ExplicitPermissionDecision;
use Avax\Components\Identity\Capabilities\Permissions\InMemoryPermissionDirectory;
use Avax\Components\Identity\Capabilities\Sessions\InMemorySessionStore;
use Avax\Components\Identity\Capabilities\Sessions\RandomSessionId;
use Avax\Components\Identity\Capabilities\Tenants\InMemoryTenantDirectory;
use Avax\Components\Identity\Capabilities\Tokens\HmacTokenCodec;
use Avax\Components\Identity\Capabilities\Tokens\InMemoryTokenBlacklist;
use Avax\Components\Identity\Configuration\Graphs\AuthenticationGraph;
use Avax\Components\Identity\Configuration\Graphs\AuthorizationGraph;
use Avax\Components\Identity\Configuration\Graphs\ExternalIdentityGraph;
use Avax\Components\Identity\Configuration\Graphs\IdentityRuntimeGraph;
use Avax\Components\Identity\Configuration\Graphs\SessionGraph;
use Avax\Components\Identity\Configuration\Graphs\TenancyGraph;
use Avax\Components\Identity\Configuration\Graphs\TokenGraph;
use Avax\Components\Identity\Flows\AuthenticatePassword\AuthenticatePassword;
use Avax\Components\Identity\Flows\AuthorizeAction\AuthorizeAction;
use Avax\Components\Identity\Flows\BeginTenantContext\BeginTenantContext;
use Avax\Components\Identity\Flows\IssueAccessToken\IssueAccessToken;
use Avax\Components\Identity\Flows\ResetIdentityRuntime\ResetIdentityRuntime;
use Avax\Components\Identity\Flows\ResolveExternalIdentity\ResolveExternalIdentity;
use Avax\Components\Identity\Flows\StartSession\StartSession;
use Avax\Components\Identity\Flows\VerifyAccessToken\VerifyAccessToken;
use Avax\Components\Identity\Foundation\Time\Clock;
use Avax\Components\Identity\Foundation\Time\NativeClock;

final class CreateIdentityRuntimeGraph
{
    public function create(IdentityConfiguration $configuration, Clock|null $clock = null): IdentityRuntimeGraph
    {
        $clock ??= new NativeClock();

        $accounts = new InMemoryUserAccountDirectory();
        $credentials = new InMemoryPasswordCredentialDirectory();
        $passwords = new NativePasswordHashing();
        $audit = new NullIdentityAuditTrail();
        $sessions = new InMemorySessionStore();
        $sessionIds = new RandomSessionId();
        $permissions = new InMemoryPermissionDirectory();
        $permissionDecision = new ExplicitPermissionDecision($permissions);
        $externalIdentities = new InMemoryExternalIdentityDirectory();
        $tenants = new InMemoryTenantDirectory();
        $tokenBlacklist = new InMemoryTokenBlacklist();
        $tokenCodec = new HmacTokenCodec($configuration->tokenSecret());

        $authentication = new AuthenticationGraph(
            new AuthenticatePassword($accounts, $credentials, $passwords, $audit, $clock),
        );

        $authorization = new AuthorizationGraph(
            new AuthorizeAction($permissionDecision, $audit, $clock),
        );

        $sessionGraph = new SessionGraph(
            new StartSession($sessionIds, $sessions, $clock),
            $sessions,
        );

        $tokenGraph = new TokenGraph(
            new IssueAccessToken($tokenCodec, $clock),
            new VerifyAccessToken($tokenCodec, $tokenBlacklist, $clock),
            $tokenBlacklist,
        );

        $externalGraph = new ExternalIdentityGraph(
            new ResolveExternalIdentity($externalIdentities),
            $externalIdentities,
        );

        $tenancyGraph = new TenancyGraph(
            new BeginTenantContext($tenants),
            $tenants,
        );

        $resetRuntime = new ResetIdentityRuntime([
            $accounts,
            $credentials,
            $sessions,
            $permissions,
            $externalIdentities,
            $tenants,
            $tokenBlacklist,
        ]);

        return new IdentityRuntimeGraph(
            authentication: $authentication,
            authorization: $authorization,
            sessions: $sessionGraph,
            tokens: $tokenGraph,
            externalIdentity: $externalGraph,
            tenancy: $tenancyGraph,
            resetRuntime: $resetRuntime,
        );
    }
}
