<?php

declare(strict_types=1);

namespace Avax\Framework\System\Capabilities\SystemDesign;

use Avax\Framework\System\Capabilities\SystemDesign\Foundation\ConsistencyFinding;

final class InspectOutboxConsistency
{
    /**
     * Check outbox consistency using runtime state.
     *
     * @param array<string, mixed> $outboxState
     * @return list<ConsistencyFinding>
     */
    public function inspect(array $outboxState = []): array
    {
        $findings = [];
        $pending = $outboxState['pending_count'] ?? 0;
        $failed = $outboxState['failed_count'] ?? 0;
        $relayed = $outboxState['relayed_count'] ?? 0;

        $total = $pending + $failed + $relayed;

        if ($pending > 100) {
            $findings[] = new ConsistencyFinding(
                'outbox_pending',
                'warning',
                "{$pending} pending outbox messages. Relay may be falling behind.",
            );
        } else {
            $findings[] = new ConsistencyFinding(
                'outbox_pending',
                'ok',
                "{$pending} pending outbox messages.",
            );
        }

        if ($failed > 0) {
            $findings[] = new ConsistencyFinding(
                'outbox_failed',
                'warning',
                "{$failed} failed outbox messages require manual intervention.",
            );
        }

        $findings[] = new ConsistencyFinding(
            'outbox_total',
            'ok',
            "Total outbox messages: {$total} (pending: {$pending}, relayed: {$relayed}, failed: {$failed}).",
        );

        return $findings;
    }
}
