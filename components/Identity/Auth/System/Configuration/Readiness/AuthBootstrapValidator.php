<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Auth\System\Configuration\Readiness;

use Avax\Components\Identity\Auth\System\Capabilities\Identity\IdentityInterface;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\Sessions\Registry\SessionRegistryInterface;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\UserSource\ProvisionableUserSourceInterface;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\UserSource\UserSourceInterface;
use Avax\Components\Identity\Auth\System\Foundation\Exceptions\ConfigurationException;
use Avax\Components\Identity\Credentials\System\Capabilities\Passkey\Support\PasskeyRuntimeInterface;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\OpenIDConnect\Protocol\OidcProviderInterface;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\SingleSignOn\Federation\FederationRuntimeInterface;
use Avax\Components\Identity\Tokens\System\Capabilities\Tokens\Runtime\Store\RefreshTokenStoreInterface;
use SensitiveParameter;

final readonly class AuthBootstrapValidator
{
    public static function validate(
        ?UserSourceInterface $userSource,
        ?IdentityInterface $identity,
        AuthCapabilityRequests $requests,
        #[SensitiveParameter]
        ?SessionRegistryInterface $sessionRegistry,
        #[SensitiveParameter]
        ?RefreshTokenStoreInterface $refreshTokenStore,
        ?PasskeyRuntimeInterface $passkeyRuntime,
        ?FederationRuntimeInterface $federationRuntime,
        ?OidcProviderInterface $oidcProvider,
        string $buildPath = 'AuthBuilder::ready()',
    ): void {
        if ($userSource === null) {
            throw ConfigurationException::missingUserSource(buildPath: $buildPath);
        }

        if ($identity === null) {
            throw ConfigurationException::missingIdentity(buildPath: $buildPath);
        }

        if ($identity->sessionIdentity() === null && $identity->jwtIdentity() === null) {
            throw ConfigurationException::missingIdentityBackend(
                buildPath: $buildPath,
                hint     : 'Use withIdentity() or withIdentityBackends() to provide a session and/or JWT backend.',
            );
        }

        if ($requests->enterpriseMode() && $sessionRegistry === null) {
            throw ConfigurationException::enterpriseSessionRegistryRequired(buildPath: $buildPath);
        }

        if ($requests->passkey() && $passkeyRuntime === null) {
            throw ConfigurationException::missingCapabilityDependency(
                capability : 'passkey',
                requirement: 'runtime',
                buildPath  : $buildPath,
                option     : 'withPasskeyRuntime()',
                cause      : 'Passkey-specific configuration was provided.',
            );
        }

        if ($requests->oidcRequestObjects() && $oidcProvider === null) {
            throw ConfigurationException::missingCapabilityDependency(
                capability : 'oidc',
                requirement: 'provider',
                buildPath  : $buildPath,
                option     : 'withOidcProvider()',
                cause      : 'OIDC request-object storage was provided without an OIDC provider.',
            );
        }

        if ($requests->oauth() && $identity->jwtIdentity() === null) {
            throw ConfigurationException::missingCapabilityDependency(
                capability : 'oauth',
                requirement: 'jwt_identity',
                buildPath  : $buildPath,
                option     : 'withIdentityBackends(jwtIdentity: ...) or withIdentity(new Identity(jwtIdentity: ...))',
                cause      : 'OAuth or OIDC configuration was provided.',
            );
        }

        if ($requests->oauth() && $refreshTokenStore === null) {
            throw ConfigurationException::missingCapabilityDependency(
                capability : 'oauth',
                requirement: 'refresh_token_store',
                buildPath  : $buildPath,
                option     : 'withRefreshTokenStore()',
                cause      : 'OAuth or OIDC configuration was provided.',
            );
        }

        if ($requests->federation() && $federationRuntime === null) {
            throw ConfigurationException::missingCapabilityDependency(
                capability : 'federation',
                requirement: 'runtime',
                buildPath  : $buildPath,
                option     : 'withFederationRuntime()',
                cause      : 'Federation-specific configuration was provided.',
            );
        }

        if ($requests->scim() && ! $userSource instanceof ProvisionableUserSourceInterface) {
            throw ConfigurationException::missingCapabilityDependency(
                capability : 'scim',
                requirement: 'provisionable_user_source',
                buildPath  : $buildPath,
                option     : 'forUser(ProvisionableUserSourceInterface)',
                cause      : 'SCIM-specific storage was provided.',
            );
        }
    }
}
