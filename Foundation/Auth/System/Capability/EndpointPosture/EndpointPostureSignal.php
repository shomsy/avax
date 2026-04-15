<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capability\EndpointPosture;

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
    public string                $detail;
    public bool                  $anomalous;
    public float                 $score;
    public EndpointPostureSignal $type;

    public function __construct(
        EndpointPostureSignal $type,
        float                 $score,
        bool                  $anomalous,
        string                $detail
    )
    {
        $this->type      = $type;
        $this->score     = $score;
        $this->anomalous = $anomalous;
        $this->detail    = $detail;
    }
}