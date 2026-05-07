<?php

declare(strict_types=1);

namespace Avax\Components\API\Surface\System\Configuration;

final readonly class RestApiConfiguration
{
    public function __construct(
        public string $basePath = '/api',
        public string $version = 'v1',
        public int    $defaultPerPage = 15,
        public int    $maxPerPage = 100,
        public bool   $includeMeta = true,
    ) {}

    public function prefix() : string
    {
        return "{$this->basePath}/{$this->version}";
    }

    public function withDefaultPerPage(int $perPage) : self
    {
        return new self(
            basePath      : $this->basePath,
            version       : $this->version,
            defaultPerPage: $perPage,
            maxPerPage    : $this->maxPerPage,
            includeMeta   : $this->includeMeta,
        );
    }
}
