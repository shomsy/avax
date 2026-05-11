<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Framework\FailureBoundary;

use Avax\Framework\System\Capabilities\FailureBoundary\Capabilities\CompileFailurePolicies\CompileFailurePolicies;
use Avax\Framework\System\Capabilities\FailureBoundary\Foundation\CompiledMethodPolicy;
use Avax\Framework\System\Capabilities\FailureBoundary\Foundation\CompiledPolicyCache;
use Avax\Framework\System\Capabilities\FailureBoundary\Foundation\FailureAction;
use Avax\Framework\System\Capabilities\FailureBoundary\Foundation\FailureDecision;
use Avax\Framework\System\Capabilities\FailureBoundary\Foundation\FailurePolicy;
use PHPUnit\Framework\TestCase;

/**
 * @no-named-arguments
 */
final class FailurePolicyCompilerTest extends TestCase
{
    protected function tearDown(): void
    {
        CompiledPolicyCache::clear();
    }

    public function testDiscoversOnFailureAttribute(): void
    {
        $compiler = new CompileFailurePolicies();
        $policy = $compiler->compile(CompilerTestController::class, 'store');

        self::assertNotNull($policy);
        self::assertSame(CompilerTestController::class, $policy->targetClass);
        self::assertSame('store', $policy->targetMethod);
        self::assertCount(2, $policy->policy->actions);

        $action = $policy->policy->findAction(\InvalidArgumentException::class);
        self::assertNotNull($action);
        self::assertSame(422, $action->statusCode);
    }

    public function testDiscoversRetryAttribute(): void
    {
        $compiler = new CompileFailurePolicies();
        $policy = $compiler->compile(CompilerTestController::class, 'fetch');

        self::assertNotNull($policy);
        self::assertSame(3, $policy->policy->retryMaxAttempts);
        self::assertSame('exponential', $policy->policy->retryBackoff);
    }

    public function testDiscoversFallbackAttribute(): void
    {
        $compiler = new CompileFailurePolicies();
        $policy = $compiler->compile(CompilerTestController::class, 'fetch');

        self::assertNotNull($policy);
        self::assertSame(TestFallbackHandler::class, $policy->policy->fallbackClass);
    }

    public function testDiscoversReportFailureAttribute(): void
    {
        $compiler = new CompileFailurePolicies();
        $policy = $compiler->compile(CompilerTestController::class, 'store');

        self::assertNotNull($policy);
        self::assertSame('http', $policy->policy->reportChannel);
    }

    public function testDiscoversDeadLetterAttribute(): void
    {
        $compiler = new CompileFailurePolicies();
        $policy = $compiler->compile(CompilerTestController::class, 'process');

        self::assertNotNull($policy);
        self::assertSame('failed_jobs', $policy->policy->deadLetterQueue);
    }

    public function testWritesCompiledMetadataToCache(): void
    {
        $compiler = new CompileFailurePolicies();
        $result = $compiler->compileAndCache(CompilerTestController::class, 'store');

        self::assertTrue($result);

        $cached = CompiledPolicyCache::get(CompilerTestController::class . '::store');
        self::assertNotNull($cached);
    }

    public function testReadsCompiledMetadataFromCache(): void
    {
        $policy = new FailurePolicy(
            actions: [
                new FailureAction(
                    exceptionClass: \RuntimeException::class,
                    decision: FailureDecision::MapToResult,
                    statusCode: 500,
                ),
            ],
        );
        $compiled = new CompiledMethodPolicy(
            targetClass: 'CachedController',
            targetMethod: 'index',
            policy: $policy,
            checksum: 'test-checksum',
            sourceMtime: 0,
            compiledAt: time(),
        );
        CompiledPolicyCache::put('CachedController::index', $compiled);

        $read = CompiledPolicyCache::get('CachedController::index');

        self::assertNotNull($read);
        self::assertSame('test-checksum', $read->checksum);
    }

    public function testInvalidatesStaleMetadata(): void
    {
        // Use a real class with an old mtime to trigger staleness
        $compiled = new CompiledMethodPolicy(
            targetClass: CompileFailurePolicies::class,
            targetMethod: 'compile',
            policy: new FailurePolicy(),
            checksum: 'old',
            sourceMtime: 1,
            compiledAt: 1,
        );
        $key = CompileFailurePolicies::class . '::compile';
        CompiledPolicyCache::put($key, $compiled);

        $read = CompiledPolicyCache::get($key);

        self::assertNull($read);
    }

    public function testCompiledPolicySerializesToArray(): void
    {
        $compiler = new CompileFailurePolicies();
        $policy = $compiler->compile(CompilerTestController::class, 'store');

        self::assertNotNull($policy);

        $array = $policy->toArray();

        self::assertArrayHasKey('schema_version', $array);
        self::assertArrayHasKey('target_class', $array);
        self::assertArrayHasKey('target_method', $array);
        self::assertArrayHasKey('checksum', $array);
        self::assertArrayHasKey('policy', $array);
        self::assertSame(1, $array['schema_version']);
    }

    public function testCompiledPolicyRoundTripsThroughSerialization(): void
    {
        $compiler = new CompileFailurePolicies();
        $original = $compiler->compile(CompilerTestController::class, 'fetch');

        self::assertNotNull($original);

        $array = $original->toArray();
        $restored = CompiledMethodPolicy::fromArray($array);

        self::assertSame($original->targetClass, $restored->targetClass);
        self::assertSame($original->targetMethod, $restored->targetMethod);
        self::assertSame($original->policy->retryMaxAttempts, $restored->policy->retryMaxAttempts);
        self::assertSame($original->policy->fallbackClass, $restored->policy->fallbackClass);
    }

    public function testReturnsNullForClassWithoutAttributes(): void
    {
        $compiler = new CompileFailurePolicies();
        $policy = $compiler->compile(CompilerTestControllerNoAttrs::class, 'index');

        self::assertNull($policy);
    }

    public function testReturnsNullForNonExistentClass(): void
    {
        $compiler = new CompileFailurePolicies();
        $policy = $compiler->compile('NonExistentClass', 'method');

        self::assertNull($policy);
    }

    public function testRejectsCorruptMetadata(): void
    {
        $corruptData = [
            'policy' => [
                'actions' => [
                    ['exception_class' => 'Exception', 'decision' => 'invalid_decision'],
                ],
            ],
        ];

        $this->expectException(\ValueError::class);

        CompiledMethodPolicy::fromArray($corruptData);
    }
}

final class CompilerTestController
{
    #[\Avax\Framework\System\Capabilities\FailureBoundary\Foundation\Attributes\OnFailure(\InvalidArgumentException::class, respondWith: 422)]
    #[\Avax\Framework\System\Capabilities\FailureBoundary\Foundation\Attributes\OnFailure(\UnexpectedValueException::class, respondWith: 401)]
    #[\Avax\Framework\System\Capabilities\FailureBoundary\Foundation\Attributes\ReportFailure(channel: 'http')]
    public function store(array $data): array
    {
        return $data;
    }

    #[\Avax\Framework\System\Capabilities\FailureBoundary\Foundation\Attributes\Retry(maxAttempts: 3, backoff: 'exponential')]
    #[\Avax\Framework\System\Capabilities\FailureBoundary\Foundation\Attributes\Fallback(TestFallbackHandler::class)]
    public function fetch(): string
    {
        return 'data';
    }

    #[\Avax\Framework\System\Capabilities\FailureBoundary\Foundation\Attributes\Retry(maxAttempts: 5)]
    #[\Avax\Framework\System\Capabilities\FailureBoundary\Foundation\Attributes\DeadLetter(queue: 'failed_jobs')]
    public function process(): void
    {
    }
}

final class CompilerTestControllerNoAttrs
{
    public function index(): string
    {
        return 'ok';
    }
}
