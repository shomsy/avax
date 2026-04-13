<?php

declare(strict_types=1);

namespace Avax\Auth\Tests\Support;

use Avax\Auth\System\Capability\Passkey\PasskeyCredential;
use Avax\Auth\System\Capability\Passkey\PasskeyRuntimeInterface;
use Avax\Auth\System\Capability\Passkey\ResolvedPasskeyCredential;
use Avax\Auth\System\Capability\Passkey\VerifiedPasskeyAuthentication;

final class FakePasskeyRuntime implements PasskeyRuntimeInterface
{
    public function beginRegistration(
        string $rpId,
        string $rpName,
        int $userId,
        string $userName,
        string $displayName,
        string $challenge,
        #[\SensitiveParameter] array $excludeCredentialIds
    ) : array
    {
        return [
            'rp_id' => $rpId,
            'rp_name' => $rpName,
            'user_id' => $userId,
            'challenge' => $challenge,
            'exclude' => $excludeCredentialIds,
        ];
    }

    public function completeRegistration(
        string $rpId,
        string $challenge,
        array $response
    ) : ResolvedPasskeyCredential
    {
        return new ResolvedPasskeyCredential(
            credentialId: (string) ($response['credential_id'] ?? 'cred-default'),
            label       : (string) ($response['label'] ?? 'Primary device')
        );
    }

    public function beginAuthentication(
        string $rpId,
        string $challenge,
        #[\SensitiveParameter] array $allowCredentialIds
    ) : array
    {
        return [
            'rp_id' => $rpId,
            'challenge' => $challenge,
            'allow' => $allowCredentialIds,
        ];
    }

    public function completeAuthentication(
        string $rpId,
        string $challenge,
        array $response,
        #[\SensitiveParameter] array $knownCredentials
    ) : VerifiedPasskeyAuthentication
    {
        $credentialId = (string) ($response['credential_id'] ?? '');
        $userId       = isset($response['user_id']) ? (int) $response['user_id'] : null;

        if ($userId === null) {
            foreach ($knownCredentials as $credential) {
                if ($credential instanceof PasskeyCredential && $credential->credentialId === $credentialId) {
                    $userId = $credential->userId;
                    break;
                }
            }
        }

        return new VerifiedPasskeyAuthentication(
            userId      : $userId ?? 0,
            credentialId: $credentialId
        );
    }
}
