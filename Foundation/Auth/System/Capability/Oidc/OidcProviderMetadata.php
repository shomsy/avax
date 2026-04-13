<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capability\Oidc;

final readonly class OidcProviderMetadata
{
    /**
     * @param list<string> $scopesSupported
     * @param list<string> $responseTypesSupported
     * @param list<string> $grantTypesSupported
     * @param list<string> $subjectTypesSupported
     * @param list<string> $idTokenSigningAlgValuesSupported
     * @param list<string> $codeChallengeMethodsSupported
     */
    public function __construct(
        public string $issuer,
        public string $authorizationEndpoint,
        public string $tokenEndpoint,
        public string $userInfoEndpoint,
        public string $jsonWebKeySetUri,
        public array $scopesSupported,
        public array $responseTypesSupported,
        public array $grantTypesSupported,
        public array $subjectTypesSupported,
        public array $idTokenSigningAlgValuesSupported,
        public array $codeChallengeMethodsSupported
    ) {}
}
