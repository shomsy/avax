<?php

declare(strict_types=1);

namespace Avax\Framework\System\Configuration\LoadConfiguration;

final class LoadConfiguration
{
    public function __construct(
        private readonly ConfigurationRepository $repository,
    ) {
    }

    public function load(array $config) : void
    {
        $this->repository->load($config);
    }
}
