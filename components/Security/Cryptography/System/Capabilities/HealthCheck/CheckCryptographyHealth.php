<?php

declare(strict_types=1);

namespace Avax\Components\Security\Cryptography\System\Capabilities\HealthCheck;

use Avax\Framework\System\Capabilities\Health\Foundation\HealthFinding;
use Avax\Framework\System\Capabilities\Health\Foundation\HealthReport;
use Avax\Framework\System\Capabilities\Health\Foundation\HealthStatus;

/**
 * CheckCryptographyHealth
 *
 * Verifies cryptography component runtime health:
 * - Required PHP extensions available (openssl)
 * - Encryption service class loadable
 * - Key manager class loadable
 */
final class CheckCryptographyHealth
{
    public function check() : HealthReport
    {
        $findings = [];
        $overall  = HealthStatus::Green;

        // Check 1: OpenSSL extension available
        if (extension_loaded('openssl')) {
            $findings[] = new HealthFinding('cryptography.openssl', HealthStatus::Green, 'OpenSSL extension available');
        } else {
            $findings[] = new HealthFinding('cryptography.openssl', HealthStatus::Red, 'OpenSSL extension not loaded');
            $overall    = HealthStatus::Red;
        }

        // Check 2: Encryption service class available
        $serviceClass = 'Avax\\Components\\Security\\Cryptography\\System\\Capabilities\\Encryption\\EncryptionService';
        if (class_exists($serviceClass)) {
            $findings[] = new HealthFinding('cryptography.encryption', HealthStatus::Green, 'Encryption service available');
        } else {
            $findings[] = new HealthFinding('cryptography.encryption', HealthStatus::Red, 'Encryption service class not loaded');
            $overall    = HealthStatus::Red;
        }

        // Check 3: Key manager available
        $keyManager = 'Avax\\Components\\Security\\Cryptography\\System\\Capabilities\\KeyManagement\\KeyManager';
        if (class_exists($keyManager)) {
            $findings[] = new HealthFinding('cryptography.key_manager', HealthStatus::Green, 'Key manager available');
        } else {
            $findings[] = new HealthFinding('cryptography.key_manager', HealthStatus::Red, 'Key manager class not loaded');
            $overall    = HealthStatus::Red;
        }

        return new HealthReport(findings: $findings, overall: $overall);
    }
}
