<?php

declare(strict_types=1);

namespace Avax\Components\Identity\PublicSurface;

use Avax\Components\Identity\Configuration\Graphs\IdentityRuntimeGraph;
use Avax\Components\Identity\Flows\AuthenticatePassword\AuthenticationResult;
use Avax\Components\Identity\Flows\AuthenticatePassword\PasswordLogin;
use Avax\Components\Identity\Flows\AuthorizeAction\AuthorizationRequest;
use Avax\Components\Identity\Flows\AuthorizeAction\AuthorizationResult;
use Avax\Components\Identity\Flows\BeginTenantContext\TenantContext;
use Avax\Components\Identity\Flows\BeginTenantContext\TenantContextRequest;
use Avax\Components\Identity\Flows\IssueAccessToken\IssueAccessTokenRequest;
use Avax\Components\Identity\Flows\IssueAccessToken\IssuedAccessToken;
use Avax\Components\Identity\Flows\ResolveExternalIdentity\ExternalIdentityRequest;
use Avax\Components\Identity\Flows\ResolveExternalIdentity\ExternalIdentityResult;
use Avax\Components\Identity\Flows\StartSession\StartedSession;
use Avax\Components\Identity\Flows\StartSession\StartSessionRequest;
use Avax\Components\Identity\Flows\VerifyAccessToken\VerifiedAccessToken;
use Avax\Components\Identity\Foundation\Values\SignedToken;

final readonly class Identity
{
    public function __construct(private IdentityRuntimeGraph $runtime) {}

    public function authenticatePassword(PasswordLogin $login): AuthenticationResult
    {
        return $this->runtime->authentication()->authenticatePassword()->authenticate($login);
    }

    public function authorize(AuthorizationRequest $request): AuthorizationResult
    {
        return $this->runtime->authorization()->authorizeAction()->authorize($request);
    }

    public function startSession(StartSessionRequest $request): StartedSession
    {
        return $this->runtime->sessions()->startSession()->start($request);
    }

    public function issueAccessToken(IssueAccessTokenRequest $request): IssuedAccessToken
    {
        return $this->runtime->tokens()->issueAccessToken()->issue($request);
    }

    public function verifyAccessToken(SignedToken $token): VerifiedAccessToken
    {
        return $this->runtime->tokens()->verifyAccessToken()->verify($token);
    }

    public function resolveExternalIdentity(ExternalIdentityRequest $request): ExternalIdentityResult
    {
        return $this->runtime->externalIdentity()->resolveExternalIdentity()->resolve($request);
    }

    public function beginTenantContext(TenantContextRequest $request): TenantContext
    {
        return $this->runtime->tenancy()->beginTenantContext()->begin($request);
    }

    public function resetRuntimeState(): void
    {
        $this->runtime->resetRuntime()->reset();
    }
}
