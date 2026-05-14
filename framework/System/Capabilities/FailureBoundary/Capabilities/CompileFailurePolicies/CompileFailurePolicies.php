<?php

declare(strict_types=1);

namespace Avax\Framework\System\Capabilities\FailureBoundary\Capabilities\CompileFailurePolicies;

use Avax\Framework\System\Capabilities\FailureBoundary\Foundation\Attributes\DeadLetter;
use Avax\Framework\System\Capabilities\FailureBoundary\Foundation\Attributes\Fallback;
use Avax\Framework\System\Capabilities\FailureBoundary\Foundation\Attributes\OnFailure;
use Avax\Framework\System\Capabilities\FailureBoundary\Foundation\Attributes\RecoverWith;
use Avax\Framework\System\Capabilities\FailureBoundary\Foundation\Attributes\ReportFailure;
use Avax\Framework\System\Capabilities\FailureBoundary\Foundation\Attributes\Rethrow;
use Avax\Framework\System\Capabilities\FailureBoundary\Foundation\Attributes\Retry;
use Avax\Framework\System\Capabilities\FailureBoundary\Foundation\Attributes\Timeout;
use Avax\Framework\System\Capabilities\FailureBoundary\Foundation\CompiledMethodPolicy;
use Avax\Framework\System\Capabilities\FailureBoundary\Foundation\CompiledPolicyCache;
use Avax\Framework\System\Capabilities\FailureBoundary\Foundation\FailureAction;
use Avax\Framework\System\Capabilities\FailureBoundary\Foundation\FailureDecision;
use Avax\Framework\System\Capabilities\FailureBoundary\Foundation\FailurePolicy;

/**
 * CompileFailurePolicies — Reads PHP attributes from a class/method and compiles them into a FailurePolicy.
 *
 * This is the only place where reflection is used. Compiled policies are cached
 * and read without reflection on subsequent requests.
 */
final readonly class CompileFailurePolicies
{
    public function compile(object|string $target, string|null $method = null): CompiledMethodPolicy|null
    {
        $className = is_string($target) ? $target : $target::class;

        if (!class_exists($className)) {
            return null;
        }

        $ref = new \ReflectionClass($className);
        $file = $ref->getFileName();
        $mtime = $file !== false && file_exists($file) ? (int) filemtime($file) : 0;

        if ($method !== null) {
            return $this->compileMethod($className, $method, $ref, $mtime);
        }

        // Compile first method with attributes (convenience for single-method targets)
        foreach ($ref->getMethods() as $methodRef) {
            $policy = $this->compileMethod($className, $methodRef->getName(), $ref, $mtime);
            if ($policy !== null) {
                return $policy;
            }
        }

        return null;
    }

    public function compileAndCache(object|string $target, string|null $method = null): bool
    {
        $policy = $this->compile($target, $method);

        if ($policy === null) {
            return false;
        }

        $key = $policy->targetClass . '::' . $policy->targetMethod;
        CompiledPolicyCache::put($key, $policy);

        return true;
    }

    /**
     * @param \ReflectionClass<object> $classRef
     */
    private function compileMethod(
        string $className,
        string $methodName,
        \ReflectionClass $classRef,
        int $mtime,
    ): CompiledMethodPolicy|null {
        if (!$classRef->hasMethod($methodName)) {
            return null;
        }

        $methodRef = $classRef->getMethod($methodName);
        $attributes = $methodRef->getAttributes();

        if (empty($attributes)) {
            return null;
        }

        $policy = $this->buildPolicyFromAttributes($attributes);
        $checksum = $this->computeChecksum($className, $methodName, $attributes);

        return new CompiledMethodPolicy(
            targetClass: $className,
            targetMethod: $methodName,
            policy: $policy,
            schemaVersion: 1,
            checksum: $checksum,
            sourceMtime: $mtime,
            compiledAt: time(),
        );
    }

    /**
     * @param list<\ReflectionAttribute<object>> $attributes
     */
    private function buildPolicyFromAttributes(array $attributes): FailurePolicy
    {
        $actions = [];
        $retryConfig = null;
        $reportConfig = null;
        $fallbackConfig = null;
        $deadLetterConfig = null;
        $timeoutConfig = null;
        $recoverConfig = null;
        $rethrowConfig = null;

        foreach ($attributes as $attr) {
            $instance = $attr->newInstance();

            match (true) {
                $instance instanceof OnFailure => $actions[] = new FailureAction(
                    exceptionClass: $instance->exceptionClass,
                    decision: FailureDecision::MapToResult,
                    statusCode: $instance->respondWith,
                    messageKey: $instance->messageKey,
                ),
                $instance instanceof Retry => $retryConfig = $instance,
                $instance instanceof ReportFailure => $reportConfig = $instance,
                $instance instanceof Fallback => $fallbackConfig = $instance,
                $instance instanceof DeadLetter => $deadLetterConfig = $instance,
                $instance instanceof Timeout => $timeoutConfig = $instance,
                $instance instanceof RecoverWith => $recoverConfig = $instance,
                $instance instanceof Rethrow => $rethrowConfig = $instance,
                default => null,
            };
        }

        return new FailurePolicy(
            actions: $actions,
            retryMaxAttempts: $retryConfig?->maxAttempts,
            retryBackoff: $retryConfig !== null ? $retryConfig->backoff : 'none',
            retryDelayMs: $retryConfig !== null ? $retryConfig->delayMs : 0,
            retryJitter: $retryConfig !== null ? $retryConfig->jitter : false,
            fallbackClass: $fallbackConfig?->handlerClass,
            reportChannel: $reportConfig?->channel,
            deadLetterQueue: $deadLetterConfig?->queue,
            timeoutMs: $timeoutConfig?->milliseconds,
            recoverWithClass: $recoverConfig?->handlerClass,
            rethrowExcept: $rethrowConfig !== null ? $rethrowConfig->except : [],
        );
    }

    /**
     * @param list<\ReflectionAttribute<object>> $attributes
     */
    private function computeChecksum(string $className, string $methodName, array $attributes): string
    {
        $data = [
            'class' => $className,
            'method' => $methodName,
            'attributes' => array_map(
                static fn(\ReflectionAttribute $a) => [
                    'name' => $a->getName(),
                    'args' => $a->getArguments(),
                ],
                $attributes,
            ),
        ];

        return hash('xxh128', serialize($data));
    }
}
