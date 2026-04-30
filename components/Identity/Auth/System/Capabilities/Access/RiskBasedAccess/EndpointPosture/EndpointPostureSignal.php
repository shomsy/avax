<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Auth\System\Capabilities\Access\RiskBasedAccess\EndpointPosture;

/**
 * Risk signal types for endpoint posture.
 */
enum EndpointPostureSignal: string
{
    case IP_REPUTATION       = 'ip_reputation';
    case GEO_VELOCITY        = 'geo_velocity';
    case IMPOSSIBLE_TRAVEL   = 'impossible_travel';
    case DEVICE_FINGERPRINT  = 'device_fingerprint';
    case BROWSER_FINGERPRINT = 'browser_fingerprint';
    case ASN_REPUTATION      = 'asn_reputation';
    case VPN_DETECTION       = 'vpn_detection';
    case PROXY_DETECTION     = 'proxy_detection';
}

/**
 * Endpoint posture signal data.
 */
final readonly class EndpointPostureSignalData
{
    public function __construct(public EndpointPostureSignal $type, public float $score, public bool $anomalous, public string $detail) {}
}
