<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Delivery\System\Configuration;

class DeliveryConfiguration implements DeliveryConfigurationInterface
{
    public function __construct(private readonly bool $enabled = true, private readonly string $environment = 'production', private readonly array $buildOptions = [])
    {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            $data['enabled'] ?? true,
            $data['environment'] ?? 'production',
            $data['build_options'] ?? [],
        );
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    public function getEnvironment(): string
    {
        return $this->environment;
    }

    public function getBuildOptions(): array
    {
        return $this->buildOptions;
    }

    public function toArray(): array
    {
        return [
            'enabled' => $this->enabled,
            'environment' => $this->environment,
            'build_options' => $this->buildOptions,
        ];
    }
}
