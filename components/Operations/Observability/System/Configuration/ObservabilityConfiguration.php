<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Observability\System\Configuration;

interface ObservabilityConfiguration
{
    public function isEnabled(): bool;

    public function getLogLevel(): string;

    public function isTracingEnabled(): bool;

    public function isMetricsEnabled(): bool;

    public function isAuditEnabled(): bool;

    public function getExporter(): ?string;
}