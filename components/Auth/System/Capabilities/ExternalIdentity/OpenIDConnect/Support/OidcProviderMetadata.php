<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capabilities\ExternalIdentity\OpenIDConnect\Support;

use SensitiveParameter;

final readonly class OidcProviderMetadata
{
    /**
     * @param list<string> $scopesSupported
     * @param list<string> $responseTypesSupported
     * @param list<string> $grantTypesSupported
     * @param list<string> $subjectTypesSupported
     * @param list<string> $idTokenSigningAlgValuesSupported
     * @param list<string> $codeChallengeMethodsSupported
     * @param list<string> $requestObjectSigningAlgValuesSupported
     * @param list<string> $authorizationResponseSigningAlgValuesSupported
     */
    public function __construct(
        public string                       $issuer,
        public string                       $authorizationEndpoint,
        #[SensitiveParameter] public string $tokenEndpoint,
        public string                       $registrationEndpoint,
        public string                       $userInfoEndpoint,
        #[SensitiveParameter] public string $endSessionEndpoint,
        public string                       $pushedAuthorizationRequestEndpoint,
        public string                       $jsonWebKeySetUri,
        public array                        $scopesSupported,
        public array                        $responseTypesSupported,
        public array                        $grantTypesSupported,
        public array                        $subjectTypesSupported,
        #[SensitiveParameter] public array  $idTokenSigningAlgValuesSupported,
        #[SensitiveParameter] public array  $codeChallengeMethodsSupported,
        public bool                         $frontChannelLogoutSupported,
        public bool                         $backChannelLogoutSupported,
        public bool                         $backChannelLogoutSessionSupported,
        public array                        $requestObjectSigningAlgValuesSupported,
        public array                        $authorizationResponseSigningAlgValuesSupported
    ) {}
}
