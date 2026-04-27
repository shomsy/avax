<?php

declare(strict_types=1);

namespace Avax\Tests\Contract\Runtime;

use function assert;

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

        $application = Avax::boot(
            builder: BuildApplication::fromProjectPath(projectPath: $this->projectRoot())
                ->withHttpHandler(
                    httpHandler: static function (RuntimeRequest $request, $runtime): string {
                        $scope    = $runtime->requestScopes()->current();
                        $previous = $scope->read(key: 'uri');
                        $scope->write(key: 'uri', value: $request->uri());

                        return sprintf(
                            'current=%s previous=%s',
                            $request->uri(),
                            $previous === null ? 'none' : $previous,
                        );
                    },
                ),
        );

        /** @var WorkerRuntimeInterface $workerRuntime */
        $workerRuntime = $factory($requests, $responses);
        $lifecycle     = $application->runtime()->runWorker(workerRuntime: $workerRuntime);

        self::assertCount(2, $responses);
        self::assertArrayHasKey(0, $responses);
        self::assertArrayHasKey(1, $responses);
        self::assertSame('current=/first previous=none', $responses[0]->response()->body());
        self::assertSame('current=/second previous=none', $responses[1]->response()->body());
        self::assertSame(['first', 'second'], $lifecycle->handledRequestIds());
        self::assertFalse($application->requestScopes()->hasCurrent());
        self::assertNull($application->context()->lastResult());
        self::assertFalse($application->state()->isBooted());
        self::assertNotNull($application->state()->shutdownAt());
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
                sender: static function (WorkerResponse $response) use (&$responses): void {
                    $responses[] = $response;
                },
            );

            assert($runtime instanceof WorkerRuntimeInterface);

            return $runtime;
        };

        return [
            'frankenphp' => [$factory(FrankenPhpRuntime::class)],
            'roadrunner' => [$factory(RoadRunnerRuntime::class)],
            'swoole'     => [$factory(SwooleRuntime::class)],
            'workerman'  => [$factory(WorkermanRuntime::class)],
        ];
    }

    private function projectRoot(): string
    {
        return dirname(__DIR__, 3);
    }
}
