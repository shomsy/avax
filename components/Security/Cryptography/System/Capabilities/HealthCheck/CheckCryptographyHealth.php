<?php

declare(strict_types=1);

namespace Avax\Components\Security\Cryptography\System\Capabilities\HealthCheck;

/**
 * CheckCryptographyHealth
 *
 * Verifies cryptography component runtime health:
 * - Cryptography service class available
 * - Required PHP extensions available
 */
final readonly class CheckCryptographyHealth
{
    public function check(): CryptographyHealthReport
    {
        $findings = [];
        $healthy = true;

        // Check 1: OpenSSL extension available
        if (extension_loaded('openssl')) {
            $findings[] = 'OpenSSL extension available';
        } else {
            $healthy = false;
            $findings[] = 'OpenSSL extension not loaded';
        }

        // Check 2: Cryptography service class available
        $serviceClass = 'Avax\\Components\\Security\\Cryptography\\System\\Capabilities\\Encryption\\EncryptionService';
        if (class_exists($serviceClass)) {
            $findings[] = 'Encryption service available';
        } else {
            $healthy = false;
            $findings[] = 'Encryption service class not loaded';
        }

        // Check 3: Key manager available
        $keyManager = 'Avax\\Components\\Security\\Cryptography\\System\\Capabilities\\KeyManagement\\KeyManager';
        if (class_exists($keyManager)) {
            $findings[] = 'Key manager available';
        } else {
            $healthy = false;
            $findings[] = 'Key manager class not loaded';
        }

        return new CryptographyHealthReport(
            healthy: $healthy,
            findings: $findings,
        );
    }
}
