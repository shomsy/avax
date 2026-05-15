<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Delivery\System\Capabilities\Health;

use Avax\Components\Operations\Delivery\System\Configuration\DeliveryConfigurationInterface;
use Avax\Framework\System\Capabilities\Health\Foundation\HealthFinding;
use Avax\Framework\System\Capabilities\Health\Foundation\HealthReport;
use Avax\Framework\System\Capabilities\Health\Foundation\HealthStatus;

class CheckDeliveryHealth
{
    public function __construct(
        private readonly ?DeliveryConfigurationInterface $deliveryConfiguration = null,
    ) {
    }

    public function check(): HealthReport
    {
        if (! $this->deliveryConfiguration instanceof DeliveryConfigurationInterface) {
            return new HealthReport(
                findings: [new HealthFinding('delivery.configuration', HealthStatus::Yellow, 'Configuration not initialized')],
                overall : HealthStatus::Yellow,
            );
        }

        $enabled = $this->deliveryConfiguration->isEnabled();

        return new HealthReport(
            findings: [
                new HealthFinding(
                    'delivery.status',
                    $enabled ? HealthStatus::Green : HealthStatus::Yellow,
                    $enabled ? 'Delivery ready' : 'Delivery disabled',
                ),
            ],
            overall: $enabled ? HealthStatus::Green : HealthStatus::Yellow,
        );
    }
}
