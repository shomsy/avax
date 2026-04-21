<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capabilities\Identity\Passkey\Support;

interface PasskeyRuntimeInterface
{
    /**
     * @param list<string> $excludeCredentialIds
     *
     * @return array<string, mixed>
     */
    public function beginRegistration(
        string $rpId,
        string $rpName,
        int    $userId,
        string $userName,
        string $displayName,
        string $challenge,
        array  $excludeCredentialIds
    ) : array;

    /**
     * @param array<string, mixed> $response
     */
    public function completeRegistration(
        string $rpId,
        string $challenge,
        array  $response
    ) : ResolvedPasskeyCredential;

    /**
     * @param list<string> $allowCredentialIds
     *
     * @return array<string, mixed>
     */
    public function beginAuthentication(
        string $rpId,
        string $challenge,
        array  $allowCredentialIds
    ) : array;

    /**
     * @param array<string, mixed>    $response
     * @param list<PasskeyCredential> $knownCredentials
     */
    public function completeAuthentication(
        string $rpId,
        string $challenge,
        array  $response,
        array  $knownCredentials
    ) : VerifiedPasskeyAuthentication;
}
