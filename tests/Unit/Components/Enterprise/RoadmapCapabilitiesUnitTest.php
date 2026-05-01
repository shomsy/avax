<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\Enterprise;

use Avax\Components\Operations\Concurrency\System\Capabilities\Cancellation\CancellationToken;
use Avax\Components\Operations\Concurrency\System\Flows\AwaitTask\AwaitTask;
use Avax\Components\Operations\Concurrency\System\Flows\RunConcurrentTasks\RunConcurrentTasks;
use Avax\Components\Operations\Observability\System\Capabilities\HealthCheck\System\Flows\LivenessProbe\LivenessProbe;
use Avax\Components\Operations\Resilience\System\Capabilities\Backoff\BackoffSchedule;
use Avax\Components\Operations\Resilience\System\Capabilities\CircuitBreaker\CircuitBreakerState;
use Avax\Components\Operations\Resilience\System\Capabilities\Retry\RetryResult;
use Avax\Components\Operations\Resilience\System\PublicSurface\Resilience;
use Avax\Components\Operations\Scheduler\System\Flows\RegisterScheduledTask\RegisterScheduledTask;
use Avax\Components\Operations\Scheduler\System\Flows\RunDueTasks\RunDueTasks;
use Avax\Components\Operations\Scheduler\System\PublicSurface\Scheduler;
use Avax\Components\Security\Secrets\System\Capabilities\Stores\EncryptedSecretStore;
use Avax\Components\Security\Secrets\System\Capabilities\Stores\InMemorySecretStore;
use Avax\Components\Security\Secrets\System\Flows\ReadSecret\ReadSecret;
use Avax\Components\Security\Secrets\System\Flows\RedactSecret\RedactSecret;
use Avax\Framework\System\Capabilities\RuntimeIsolation\RuntimeIsolationGuard;
use Avax\Framework\System\Capabilities\TestingFakes\TaskFake;
use Avax\Framework\System\Foundation\Result\Failure;
use Avax\Framework\System\Foundation\Result\Result;
use PHPUnit\Framework\TestCase;
use RuntimeException;

final class RoadmapCapabilitiesUnitTest extends TestCase
{
    public function test_retry_builder_retries_until_operation_succeeds() : void
    {
        $attempts = 0;

        $result = Resilience::retry(function () use (&$attempts) {
            $attempts++;

            if ($attempts < 2) {
                throw new RuntimeException('temporary failure');
            }

            return 'ok';
        })->times(attempts: 3)->backoff(milliseconds: 0)->run();

        $this->assertInstanceOf(expected: RetryResult::class, actual: $result);
        $this->assertTrue(condition: $result->success);
        $this->assertSame(expected: 'ok', actual: $result->result);
        $this->assertSame(expected: 2, actual: $result->attempts);
    }

    public function test_circuit_breaker_opens_after_threshold_failure() : void
    {
        $breaker = Resilience::circuitBreaker(failureThreshold: 1, cooldownSeconds: 60);

        try {
            $breaker->run(static fn () => throw new RuntimeException('downstream failed'));
        } catch (RuntimeException) {
        }

        $this->assertSame(expected: CircuitBreakerState::Open, actual: $breaker->state());
    }

    public function test_backoff_schedule_caps_exponential_delay() : void
    {
        $schedule = new BackoffSchedule(baseMilliseconds: 100, maximumMilliseconds: 250);

        $this->assertSame(expected: 100, actual: $schedule->delayForAttempt(attempt: 1));
        $this->assertSame(expected: 200, actual: $schedule->delayForAttempt(attempt: 2));
        $this->assertSame(expected: 250, actual: $schedule->delayForAttempt(attempt: 4));
    }

    public function test_concurrency_flows_run_and_await_tasks() : void
    {
        $results = (new RunConcurrentTasks())->run([
                                                       static fn () => 'a',
                                                       static fn () => 'b',
                                                   ]);

        $this->assertSame(expected: ['a', 'b'], actual: $results);
        $this->assertSame(expected: 'done', actual: (new AwaitTask())->await(static fn () => 'done'));
    }

    public function test_cancellation_token_records_cancelled_state() : void
    {
        $token = new CancellationToken();

        $this->assertFalse(condition: $token->isCancelled());

        $token->cancel();

        $this->assertTrue(condition: $token->isCancelled());
    }

    public function test_scheduler_registers_and_runs_due_tasks() : void
    {
        Scheduler::clear();
        $ran = false;

        (new RegisterScheduledTask())->register(
            expression: '* * * * *',
            task      : function () use (&$ran) : void {
                $ran = true;
            },
        );

        $report = (new RunDueTasks())->run();

        $this->assertTrue(condition: $ran);
        $this->assertSame(expected: 1, actual: $report->count());
        Scheduler::clear();
    }

    public function test_secret_store_reads_redacts_and_encrypts_values() : void
    {
        $store = new InMemorySecretStore();
        $store->set(key: 'api-key', value: 'supersecret');

        $this->assertSame(expected: 'supersecret', actual: (new ReadSecret(store: $store))->read(key: 'api-key'));
        $this->assertSame(expected: 'su*******et', actual: (new RedactSecret())->redact(value: 'supersecret'));

        $encrypted = new EncryptedSecretStore(
            inner        : new InMemorySecretStore(),
            encryptionKey: str_repeat(string: 'a', times: 32),
        );
        $encrypted->set(key: 'token', value: 'secret-value');

        $this->assertSame(expected: 'secret-value', actual: $encrypted->get(key: 'token'));
    }

    public function test_health_liveness_probe_returns_ok_report() : void
    {
        $report = (new LivenessProbe())->read();

        $this->assertSame(expected: 'ok', actual: $report->status);
    }

    public function test_runtime_isolation_guard_detects_runtime_api_leaks_outside_adapters() : void
    {
        $guard = new RuntimeIsolationGuard();

        $this->assertSame(
            expected: ['Swoole\\'],
            actual  : $guard->detectLeaks(source: 'new Swoole\\Http\\Server()', path: 'framework/System/Flows/HandleIncomingHttp.php'),
        );
        $this->assertSame(
            expected: [],
            actual  : $guard->detectLeaks(source: 'new Swoole\\Http\\Server()', path: 'framework/System/Capabilities/Runtime/Adapters/Swoole/SwooleRuntime.php'),
        );
    }

    public function test_task_fake_records_dispatched_tasks() : void
    {
        $fake = new TaskFake();
        $task = new class {};

        $fake->dispatch(task: $task);

        $this->assertSame(expected: [$task], actual: $fake->dispatched());
        $fake->assertDispatched(taskClass: $task::class);
    }

    public function test_result_primitive_carries_success_or_failure() : void
    {
        $ok      = Result::ok(value: 'done');
        $failure = Result::fail(new Failure(code: 'runtime.failed', message: 'Runtime failed'));

        $this->assertTrue(condition: $ok->succeeded());
        $this->assertSame(expected: 'done', actual: $ok->value());
        $this->assertFalse(condition: $failure->succeeded());
        $this->assertSame(expected: 'runtime.failed', actual: $failure->failure()?->code);
    }
}
