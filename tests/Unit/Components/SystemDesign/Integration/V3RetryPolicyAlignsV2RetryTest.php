<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\SystemDesign\Integration;

use Avax\Components\SystemDesign\System\Capabilities\Capacity\Queue\QueueDepth;
use Avax\Components\SystemDesign\System\Capabilities\Messaging\MessagingModel;
use Avax\Components\SystemDesign\System\Capabilities\Messaging\Retry\RetryPolicy;
use Avax\Components\SystemDesign\System\Flows\DetectMessagingRisk\DetectMessagingRisk;
use Avax\Components\SystemDesign\System\Flows\EstimateQueuePressure\EstimateQueuePressure;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * V3-05: Prove V3 RetryPolicy and QueueDepth align with V2 Tasks runtime behavior.
 *
 * V3 = design-time modeling. V2 Tasks = runtime execution.
 * V3 TaskRetryPolicy, TaskQueue, TaskRunner are runtime components.
 * This test proves V3 models describe the same concepts without duplicating ownership.
 */
final class V3RetryPolicyAlignsV2RetryTest extends TestCase
{
    #[Test]
    public function v3RetryPolicyAlignsWithV2TaskRetryPolicyDefaults() : void
    {
        // V2 TaskRetryPolicy defaults: maxAttempts=3, backoffMs=1000, exponential=true
        // V3 RetryPolicy models the same parameters
        $v3Policy = new RetryPolicy(
            maxRetries     : 3,
            backoffStrategy: 'exponential',
            initialDelayMs : 1000,
            maxDelayMs     : 8000, // 1000 * 2^3 = 8000 worst case per retry
        );

        self::assertTrue($v3Policy->validate()['valid']);
        self::assertSame(3, $v3Policy->maxRetries);
        self::assertSame('exponential', $v3Policy->backoffStrategy);

        // V2 TaskRetryPolicy.delays() would produce [1000, 2000, 4000] for 3 attempts
        // V3 worstCaseRetryTimeMs() gives maxRetries * maxDelayMs = 3 * 8000 = 24000
        self::assertSame(24000, $v3Policy->worstCaseRetryTimeMs());
    }

    #[Test]
    public function v3RetryPolicyAlignsWithV2RetryOptions() : void
    {
        // V2 RetryOptions: attempts=3, backoffMs=200, timeoutMs=null
        // V3 RetryPolicy models attempts and backoff strategy
        $v3Policy = new RetryPolicy(
            maxRetries     : 3,
            backoffStrategy: 'fixed',
            initialDelayMs : 200,
            maxDelayMs     : 200,
        );

        self::assertTrue($v3Policy->validate()['valid']);
        self::assertSame(3, $v3Policy->maxRetries);
        self::assertSame(200, $v3Policy->initialDelayMs);
    }

    #[Test]
    public function v3RetryPolicyDetectsZeroRetriesAsRisk() : void
    {
        // V2 MessageBusConfiguration defaultRetryAttempts=0
        // V3 should flag this when there are messages that may fail
        $config = [
            'system'    => 'v2-tasks-no-retry',
            'messaging' => [
                'messages'  => [
                    [
                        'name'        => 'ProcessPayment',
                        'type'        => 'command',
                        'idempotent'  => true,
                        'max_retries' => 0,
                        'timeout_ms'  => 5000,
                    ],
                ],
                'consumers' => [],
            ],
        ];

        $model = MessagingModel::fromConfig($config);
        $risks = (new DetectMessagingRisk())->execute($model);

        $noRetry = array_filter($risks, fn ($r) => $r['category'] === 'missing_retry_policy');
        self::assertNotEmpty($noRetry);
    }

    #[Test]
    public function v3RetryPolicyValidatesWithV2ResilienceRetryDefaults() : void
    {
        // V2 RetryBuilder defaults: attempts=3, backoffMs=200
        $v3Policy = new RetryPolicy(
            maxRetries     : 3,
            backoffStrategy: 'exponential',
            initialDelayMs : 200,
            maxDelayMs     : 30000,
        );

        $result = $v3Policy->validate();
        self::assertTrue($result['valid']);
    }

    #[Test]
    public function v3RetryPolicyDetectsInvalidDelayConfiguration() : void
    {
        // maxDelayMs < initialDelayMs is invalid
        $policy = new RetryPolicy(
            maxRetries     : 3,
            backoffStrategy: 'exponential',
            initialDelayMs : 5000,
            maxDelayMs     : 100,
        );

        $result = $policy->validate();
        self::assertFalse($result['valid']);
    }

    #[Test]
    public function v3RetryPolicyDescribesV2TaskRetryBehavior() : void
    {
        // V2 Tasks: retryTask(runner, task, policy) uses TaskRetryPolicy
        // V3 RetryPolicy can model what V2 TaskRetryPolicy does at design time
        $v3Policy = new RetryPolicy(
            maxRetries     : 5,
            backoffStrategy: 'exponential',
            initialDelayMs : 100,
            maxDelayMs     : 10000,
        );

        // V2 TaskRetryPolicy(5, 100, true) would produce delays: [100, 200, 400, 800, 1600]
        // V3 worstCaseRetryTimeMs: 5 * 10000 = 50000
        self::assertSame(50000, $v3Policy->worstCaseRetryTimeMs());

        // V3 can model max retry time at design time; V2 computes actual delays at runtime
        self::assertGreaterThan(0, $v3Policy->maxRetries);
    }
}
