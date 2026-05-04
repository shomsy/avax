<?php

declare(strict_types=1);

namespace Avax\Components\SystemDesign\Failure\System\Flows\AnalyzeFailureMode;

use Avax\Components\SystemDesign\Failure\System\PublicSurface\FailureMode;

final readonly class AnalyzeFailureMode
{
    /**
     * @return array<string, mixed>
     */
    public function __invoke(FailureMode $mode): array
    {
        return [
            'mode' => $mode->value,
            'recoverable' => match ($mode) {
                FailureMode::TIMEOUT => true,
                FailureMode::CIRCUIT_OPEN => true,
                FailureMode::RATE_LIMIT => true,
                FailureMode::PARTITION => false,
                FailureMode::CORRUPTION => false,
                FailureMode::DUPLICATE => true,
                FailureMode::REORDERING => false,
            },
            'idempotent' => match ($mode) {
                FailureMode::TIMEOUT => true,
                FailureMode::CIRCUIT_OPEN => true,
                FailureMode::RATE_LIMIT => true,
                FailureMode::PARTITION => false,
                FailureMode::CORRUPTION => false,
                FailureMode::DUPLICATE => false,
                FailureMode::REORDERING => false,
            },
            'impact' => match ($mode) {
                FailureMode::TIMEOUT => 'delay',
                FailureMode::CIRCUIT_OPEN => 'unavailability',
                FailureMode::RATE_LIMIT => 'rejection',
                FailureMode::PARTITION => 'inconsistency',
                FailureMode::CORRUPTION => 'data-loss',
                FailureMode::DUPLICATE => 'over-processing',
                FailureMode::REORDERING => 'ordering-violation',
            },
        ];
    }
}
