<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capability\Oidc;

use SensitiveParameter;

final readonly class OidcProviderMetadata
{
    /** @var list<string> */
    public array  $authorizationResponseSigningAlgValuesSupported;
    /** @var list<string> */
    public array  $requestObjectSigningAlgValuesSupported;
    public bool   $backChannelLogoutSessionSupported;
    public bool   $backChannelLogoutSupported;
    public bool   $frontChannelLogoutSupported;
    /** @var list<string> */
    public array  $codeChallengeMethodsSupported;
    /** @var list<string> */
    public array  $idTokenSigningAlgValuesSupported;
    /** @var list<string> */
    public array  $subjectTypesSupported;
    /** @var list<string> */
    public array  $grantTypesSupported;
    /** @var list<string> */
    public array  $responseTypesSupported;
    /** @var list<string> */
    public array  $scopesSupported;
    public string $jsonWebKeySetUri;
    public string $pushedAuthorizationRequestEndpoint;
    public string $endSessionEndpoint;
    public string $userInfoEndpoint;
    public string $registrationEndpoint;
    public string $tokenEndpoint;
    public string $authorizationEndpoint;
    public string $issuer;

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
        string                       $issuer,
        string                       $authorizationEndpoint,
        #[SensitiveParameter] string $tokenEndpoint,
        string                       $registrationEndpoint,
        string                       $userInfoEndpoint,
        #[SensitiveParameter] string $endSessionEndpoint,
        string                       $pushedAuthorizationRequestEndpoint,
        string                       $jsonWebKeySetUri,
        array                        $scopesSupported,
        array                        $responseTypesSupported,
        array                        $grantTypesSupported,
        array                        $subjectTypesSupported,
        #[SensitiveParameter] array  $idTokenSigningAlgValuesSupported,
        #[SensitiveParameter] array  $codeChallengeMethodsSupported,
        bool                         $frontChannelLogoutSupported,
        bool                         $backChannelLogoutSupported,
        bool                         $backChannelLogoutSessionSupported,
        array                        $requestObjectSigningAlgValuesSupported,
        array                        $authorizationResponseSigningAlgValuesSupported
    )
    {
        $this->issuer                                         = $issuer;
        $this->authorizationEndpoint                          = $authorizationEndpoint;
        $this->tokenEndpoint                                  = $tokenEndpoint;
        $this->registrationEndpoint                           = $registrationEndpoint;
        $this->userInfoEndpoint                               = $userInfoEndpoint;
        $this->endSessionEndpoint                             = $endSessionEndpoint;
        $this->pushedAuthorizationRequestEndpoint             = $pushedAuthorizationRequestEndpoint;
        $this->jsonWebKeySetUri                               = $jsonWebKeySetUri;
        $this->scopesSupported                                = $scopesSupported;
        $this->responseTypesSupported                         = $responseTypesSupported;
        $this->grantTypesSupported                            = $grantTypesSupported;
        $this->subjectTypesSupported                          = $subjectTypesSupported;
        $this->idTokenSigningAlgValuesSupported               = $idTokenSigningAlgValuesSupported;
        $this->codeChallengeMethodsSupported                  = $codeChallengeMethodsSupported;
        $this->frontChannelLogoutSupported                    = $frontChannelLogoutSupported;
        $this->backChannelLogoutSupported                     = $backChannelLogoutSupported;
        $this->backChannelLogoutSessionSupported              = $backChannelLogoutSessionSupported;
        $this->requestObjectSigningAlgValuesSupported         = $requestObjectSigningAlgValuesSupported;
        $this->authorizationResponseSigningAlgValuesSupported = $authorizationResponseSigningAlgValuesSupported;
    }
}
