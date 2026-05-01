<?php

declare(strict_types=1);

namespace Avax\Tests\Contract\Runtime;

use Avax\Framework\System\Capabilities\Runtime\Adapters\FrankenPhp\FrankenPhpRuntime;
use Avax\Framework\System\Capabilities\Runtime\Adapters\RoadRunner\RoadRunnerRuntime;
use Avax\Framework\System\Capabilities\Runtime\Adapters\Swoole\SwooleRuntime;
use Avax\Framework\System\Capabilities\Runtime\Adapters\Workerman\WorkermanRuntime;
use Avax\Framework\System\Capabilities\Runtime\RuntimeRequest;
use Avax\Framework\System\Capabilities\Runtime\Worker\WorkerRequest;
use Avax\Framework\System\Capabilities\Runtime\Worker\WorkerResponse;
use Avax\Framework\System\Capabilities\Runtime\Worker\WorkerRuntimeInterface;
use Avax\Framework\System\Configuration\BuildApplication\BuildApplication;
use Avax\Framework\System\PublicSurface\Avax;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use function assert;

final class WorkerRuntimeContractTest extends TestCase
{
    #[DataProvider('workerRuntimes')]
    public function test_it_resets_request_state_between_two_worker_requests(callable $factory): void
    {
        /** @var list<WorkerRequest> $requests */
        $requests = [
            new WorkerRequest(
                id     : 'first',
                request: new RuntimeRequest(method: 'GET', uri: '/first'),
            ),
            new WorkerRequest(
                id     : 'second',
                request: new RuntimeRequest(method: 'GET', uri: '/second'),
            ),
        ];
        /** @var list<WorkerResponse> $responses */
        $responses = [];

        $avax            = Avax::boot(
            builder: BuildApplication::fromProjectPath(projectPath: $this->projectRoot())
                ->withHttpHandler(
                    httpHandler: static function (RuntimeRequest $runtimeRequest, $runtime) : string {
                        $scope = $runtime->requestScopes()->current();
                        $previous = $scope->read(key: 'uri');
                        $scope->write(key: 'uri', value: $runtimeRequest->uri());

                        return sprintf(
                            'current=%s previous=%s',
                            $runtimeRequest->uri(),
                            $previous ?? 'none',
                        );
                    },
                ),
        );

        /** @var WorkerRuntimeInterface $workerRuntime */
        $workerRuntime = $factory($requests, $responses);
        $workerLifecycle = $avax->runtime()->runWorker(workerRuntime: $workerRuntime);

        self::assertCount(2, $responses);
        self::assertArrayHasKey(0, $responses);
        self::assertArrayHasKey(1, $responses);
        self::assertSame('current=/first previous=none', $responses[0]->response()->body());
        self::assertSame('current=/second previous=none', $responses[1]->response()->body());
        self::assertSame(['first', 'second'], $workerLifecycle->handledRequestIds());
        self::assertFalse($avax->requestScopes()->hasCurrent());
        self::assertNull($avax->context()->lastResult());
        self::assertFalse($avax->state()->isBooted());
        self::assertNotNull($avax->state()->shutdownAt());
    }

    /**
     * @return array<string, array{callable(array<WorkerRequest>, array<int, WorkerResponse>): WorkerRuntimeInterface}>
     */
    public static function workerRuntimes(): array
    {
        $factory = static fn (string $adapterClass): callable => static function (array &$requests, array &$responses) use ($adapterClass): WorkerRuntimeInterface {
            $runtime = new $adapterClass(
                receiver: static function () use (&$requests): WorkerRequest|null {
                    return array_shift($requests);
                },
                sender  : static function (WorkerResponse $workerResponse) use (&$responses) : void {
                    $responses[] = $workerResponse;
                },
            );

            assert($runtime instanceof WorkerRuntimeInterface);

            return $runtime;
        };

        return [
            'frankenphp' => [$factory(FrankenPhpRuntime::class)],
            'roadrunner' => [$factory(RoadRunnerRuntime::class)],
            'swoole'    => [$factory(SwooleRuntime::class)],
            'workerman' => [$factory(WorkermanRuntime::class)],
        ];
    }

    private function projectRoot(): string
    {
        return dirname(__DIR__, 3);
    }
}
