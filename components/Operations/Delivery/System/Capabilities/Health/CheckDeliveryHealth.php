<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Delivery\System\Capabilities\Health;

use Avax\Components\Operations\Delivery\System\Configuration\DeliveryConfigurationInterface;

class CheckDeliveryHealth
{
    public function __construct(
        private readonly ?DeliveryConfigurationInterface $deliveryConfiguration = null,
    )
    {
    }

    public function check(): DeliveryHealthReport
    {
        if (!$this->deliveryConfiguration instanceof DeliveryConfigurationInterface) {
            return new DeliveryHealthReport(
                healthy: false,
                message: 'Configuration not initialized',
                details: [],
            );
        }

        return new DeliveryHealthReport(
            healthy: $this->deliveryConfiguration->isEnabled(),
            message: $this->deliveryConfiguration->isEnabled() ? 'Delivery ready' : 'Delivery disabled',
            details: [
                'environment' => $this->deliveryConfiguration->getEnvironment(),
                'build_options' => $this->deliveryConfiguration->getBuildOptions(),
            ],
        );
    }
}
