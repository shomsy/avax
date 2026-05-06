<?php

declare(strict_types=1);

namespace Avax\Framework\System\Configuration\LoadConfiguration;

final readonly class LoadConfiguration
{
    public function __construct(
        private ConfigurationRepository $configurationRepository,
    ) {
    }

    /**
     * @param  array<string, mixed>  $config
     */
    public function load(array $config): void
    {
        $this->configurationRepository->load($config);
    }
}
