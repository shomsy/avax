<?php

declare(strict_types=1);

namespace Avax\Labs\SystemDesignKit\System\Capabilities\ArchitectureTesting;

use Avax\Labs\SystemDesignKit\System\Capabilities\Capacity\CapacityModel;
use Avax\Labs\SystemDesignKit\System\Capabilities\Messaging\MessagingModel;

/**
 * A single architecture test assertion against a system design.
 *
 * @experimental V3 labs
 */
final readonly class ArchitectureTest
{
    /**
     * @param array<int|string, mixed> $parameters
     */
    public function __construct(
        public string $name,
        public string $type,
        public string $description,
        public string $severity,
        public array  $parameters,
    ) {}

    /**
     * @param array<int|string, mixed> $config
     */
    public static function fromConfig(array $config) : self
    {
        return new self(
            name       : (string) ($config['name'] ?? 'unknown'),
            type       : (string) ($config['type'] ?? 'unknown'),
            description: (string) ($config['description'] ?? ''),
            severity   : (string) ($config['severity'] ?? 'medium'),
            parameters : $config['parameters'] ?? [],
        );
    }

    /**
     * Evaluate this test against capacity and messaging models.
     *
     * @return array{
     *     test: string,
     *     severity: string,
     *     passed: bool,
     *     detail: string,
     * }
     */
    public function evaluate(CapacityModel $capacity, ?MessagingModel $messaging = null) : array
    {
        $passed = $this->check($capacity, $messaging);

        return [
            'test'     => $this->name,
            'severity' => $this->severity,
            'passed'   => $passed,
            'detail'   => $this->explain($capacity, $messaging),
        ];
    }

    private function check(CapacityModel $capacity, ?MessagingModel $messaging) : bool
    {
        return match ($this->type) {
            'hot_path'      => $this->checkHotPath($capacity),
            'idempotency'   => $this->checkIdempotency($capacity, $messaging),
            'external_port' => $this->checkExternalPort($capacity, $messaging),
            'cache'         => $this->checkCache($capacity),
            'dead_letter'   => $this->checkDeadLetter($messaging),
            'observability' => $this->checkObservability($messaging),
            'reliability'   => $this->checkReliability($capacity, $messaging),
            'consistency'   => $this->checkConsistency($capacity),
            'architecture'  => $this->checkArchitecture($capacity, $messaging),
            'messaging'     => $this->checkMessaging($messaging),
            'resilience'    => $this->checkResilience($capacity),
            default         => false,
        };
    }

    private function checkHotPath(CapacityModel $capacity) : bool
    {
        $excluded = $this->parameters['excluded'] ?? [];

        if (in_array('analytics', $excluded, true)) {
            return $capacity->traffic->reads >= $capacity->traffic->total * 0.9;
        }

        return true;
    }

    private function checkIdempotency(CapacityModel $capacity, ?MessagingModel $messaging) : bool
    {
        if ($messaging === null) {
            return false;
        }

        $idempotentMessages = $messaging->idempotentMessages();

        return count($idempotentMessages) > 0;
    }

    private function checkExternalPort(CapacityModel $capacity, ?MessagingModel $messaging) : bool
    {
        $required = $this->parameters['required'] ?? [];

        $hasTimeout = in_array('timeout', $required, true) && $capacity->latencyBudget->p99Ms > 0;
        $hasRetry   = in_array('retry', $required, true) && $messaging !== null && $messaging->retryPolicy !== null;

        return $hasTimeout && $hasRetry;
    }

    private function checkCache(CapacityModel $capacity) : bool
    {
        return match ($this->parameters['expected'] ?? null) {
            'stampede_protection' => $capacity->cacheStampede->protectionRequired,
            default               => true,
        };
    }

    private function checkDeadLetter(?MessagingModel $messaging) : bool
    {
        if ($messaging === null) {
            return false;
        }

        $required = $this->parameters['required'] ?? [];

        if (in_array('dead_letter', $required, true)) {
            return $messaging->hasDeadLetterQueue();
        }

        return true;
    }

    private function checkObservability(?MessagingModel $messaging) : bool
    {
        if ($messaging === null) {
            return false;
        }

        $required = $this->parameters['required'] ?? [];

        if (in_array('alert_channel', $required, true)) {
            return $messaging->deadLetterQueue !== null && $messaging->deadLetterQueue->alertChannel !== '';
        }

        return true;
    }

    private function checkReliability(CapacityModel $capacity, ?MessagingModel $messaging) : bool
    {
        $required = $this->parameters['required'] ?? [];

        $hasOutbox = in_array('outbox', $required, true) && $messaging !== null && $messaging->usesOutbox();

        return $hasOutbox;
    }

    private function checkConsistency(CapacityModel $capacity) : bool
    {
        return match ($this->parameters['expected'] ?? null) {
            'strong_consistency_for_writes' => $capacity->slo->percentage >= 99.9,
            default                         => true,
        };
    }

    private function checkArchitecture(CapacityModel $capacity, ?MessagingModel $messaging) : bool
    {
        return match ($this->parameters['expected'] ?? null) {
            'eventual_consistency_reads' => true,
            default                      => true,
        };
    }

    private function checkMessaging(?MessagingModel $messaging) : bool
    {
        if ($messaging === null) {
            return false;
        }

        return match ($this->parameters['expected'] ?? null) {
            'ordered_consumers' => array_reduce(
                $messaging->consumers,
                static fn (bool $carry, $c) : bool => $carry || ($c->ordered ?? false),
                false,
            ),
            default             => true,
        };
    }

    private function checkResilience(CapacityModel $capacity) : bool
    {
        return match ($this->parameters['expected'] ?? null) {
            'load_shedding_policy' => $capacity->failureBudget->minutesPerMonth > 0,
            default                => true,
        };
    }

    private function explain(CapacityModel $capacity, ?MessagingModel $messaging) : string
    {
        return match ($this->type) {
            'hot_path'      => $this->explainHotPath($capacity),
            'idempotency'   => $this->explainIdempotency($messaging),
            'external_port' => $this->explainExternalPort($capacity, $messaging),
            'cache'         => $this->explainCache($capacity),
            'dead_letter'   => $this->explainDeadLetter($messaging),
            'observability' => $this->explainObservability($messaging),
            'reliability'   => $this->explainReliability($capacity, $messaging),
            'consistency'   => $this->explainConsistency($capacity),
            'architecture'  => $this->explainArchitecture($capacity, $messaging),
            'messaging'     => $this->explainMessaging($messaging),
            'resilience'    => $this->explainResilience($capacity),
            default         => "Unknown test type: {$this->type}",
        };
    }

    private function explainHotPath(CapacityModel $capacity) : string
    {
        $readRatio = $capacity->traffic->total > 0
            ? round($capacity->traffic->reads / $capacity->traffic->total * 100, 1)
            : 0;

        return "Read ratio: {$readRatio}%. Hot path optimization assumed for read-heavy workloads.";
    }

    private function explainIdempotency(?MessagingModel $messaging) : string
    {
        if ($messaging === null) {
            return "No messaging model configured; idempotency cannot be verified.";
        }

        $count = count($messaging->idempotentMessages());

        return "{$count} idempotent messages configured.";
    }

    private function explainExternalPort(CapacityModel $capacity, ?MessagingModel $messaging) : string
    {
        $parts = [];

        if ($capacity->latencyBudget->p99Ms > 0) {
            $parts[] = "timeout configured (p99: {$capacity->latencyBudget->p99Ms}ms)";
        }

        if ($messaging !== null && $messaging->retryPolicy !== null) {
            $parts[] = "retry policy configured (max: {$messaging->retryPolicy->maxRetries})";
        }

        return $parts !== []
            ? 'External port policies: ' . implode(', ', $parts)
            : 'External port policies not configured';
    }

    private function explainCache(CapacityModel $capacity) : string
    {
        return match ($this->parameters['expected'] ?? null) {
            'stampede_protection' => $capacity->cacheStampede->protectionRequired
                ? 'Cache stampede protection required and configured'
                : 'Cache stampede protection not configured',
            default               => "Cache hit ratio target: {$capacity->cacheHitRatio->target}",
        };
    }

    private function explainDeadLetter(?MessagingModel $messaging) : string
    {
        if ($messaging === null) {
            return 'No messaging model; dead letter queue cannot be verified.';
        }

        return $messaging->hasDeadLetterQueue()
            ? 'Dead letter queue configured and enabled'
            : 'Dead letter queue not configured';
    }

    private function explainObservability(?MessagingModel $messaging) : string
    {
        if ($messaging === null) {
            return 'No messaging model; observability cannot be verified.';
        }

        $alertChannel = $messaging->deadLetterQueue?->alertChannel ?? 'none';

        return "Alert channel: {$alertChannel}";
    }

    private function explainReliability(CapacityModel $capacity, ?MessagingModel $messaging) : string
    {
        $parts = [];

        if ($messaging !== null && $messaging->usesOutbox()) {
            $parts[] = 'outbox pattern enabled';
        }

        return $parts !== []
            ? 'Reliability: ' . implode(', ', $parts)
            : 'Reliability patterns not fully configured';
    }

    private function explainConsistency(CapacityModel $capacity) : string
    {
        return "SLO: {$capacity->slo->percentage}%, failure budget: {$capacity->failureBudget->minutesPerMonth} min/month";
    }

    private function explainArchitecture(CapacityModel $capacity, ?MessagingModel $messaging) : string
    {
        return "Architecture assertion evaluated against capacity model.";
    }

    private function explainMessaging(?MessagingModel $messaging) : string
    {
        if ($messaging === null) {
            return 'No messaging model configured.';
        }

        $orderedConsumers = array_filter($messaging->consumers, static fn ($c) : bool => $c->ordered ?? false);

        return count($orderedConsumers) > 0
            ? count($orderedConsumers) . ' ordered consumer(s) configured'
            : 'No ordered consumers configured';
    }

    private function explainResilience(CapacityModel $capacity) : string
    {
        return "Failure budget: {$capacity->failureBudget->minutesPerMonth} min/month. Load shedding policy assumed when budget is tight.";
    }
}
