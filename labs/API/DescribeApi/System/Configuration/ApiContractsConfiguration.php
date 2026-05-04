<?php

declare(strict_types=1);

namespace Avax\Labs\API\DescribeApi\System\Configuration;

interface ApiContractsConfiguration
{
    public function getVersion(): string;

    public function getTitle(): string;

    public function getDescription(): string;

    public function isStrictMode(): bool;

    public function getBasePath(): string;
}