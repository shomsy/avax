<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/bootstrap.php';

use Avax\Container\ContainerInterface;
use Avax\Container\DependencyInjection\Injection\Attributes\RuntimeInput;
use Avax\Container\Errors\ContainerException;

interface StoryInternalContract
{
}

final class StoryInternalService implements StoryInternalContract
{
}

final class StoryFlowEntry
{
    public function __construct(public StoryInternalContract $dependency)
    {
    }
}

final class StoryFlowLocalService
{
}

final class RequestOnlyStoryService
{
}

final class StoryRuntimeInputConsumer
{
    public function __construct(#[RuntimeInput('token')] public string $token)
    {
    }
}

final class StoryLocatorDrift
{
    public function __construct(public ContainerInterface $container)
    {
    }
}

$topLevelContainer = makeTestContainer();
$topLevelContainer->bind(StoryFlowLocalService::class, StoryFlowLocalService::class)
    ->asFlow('login')
    ->asPrivate();

try {
    $topLevelContainer->get(StoryFlowLocalService::class);
    throw new RuntimeException('Flow-local services should not resolve from the top-level surface.');
} catch (ContainerException $exception) {
    assertTrue(
        str_contains($exception->getMessage(), 'is not part of the top-level container surface'),
        'Top-level access failures should explain the surface violation.'
    );
    assertTrue(
        str_contains($exception->getMessage(), 'Likely fix: mark the flow root as entry(), export the shared capability'),
        'Top-level access failures should suggest an ownership-aware fix.'
    );
}

$scopedContainer = makeTestContainer();
$scopedContainer->scoped(RequestOnlyStoryService::class, RequestOnlyStoryService::class)->request();

try {
    $scopedContainer->get(RequestOnlyStoryService::class);
    throw new RuntimeException('Request-scoped services should require an active request scope.');
} catch (ContainerException $exception) {
    assertTrue(
        str_contains($exception->getMessage(), 'requires an active [request] scope'),
        'Scope failures should name the missing required scope.'
    );
    assertTrue(
        str_contains($exception->getMessage(), "openScope('request') before resolving it"),
        'Scope failures should suggest the exact scope operation to open.'
    );
}

$crossSliceContainer = makeTestContainer();
$crossSliceContainer->singleton(StoryInternalContract::class, StoryInternalService::class)
    ->asCapability('payments')
    ->asInternal();
$crossSliceContainer->bind(StoryFlowEntry::class, StoryFlowEntry::class)
    ->asFlow('checkout')
    ->asPrivate()
    ->entry();

try {
    $crossSliceContainer->get(StoryFlowEntry::class);
    throw new RuntimeException('Cross-slice internal dependencies should be blocked.');
} catch (ContainerException $exception) {
    assertTrue(
        str_contains($exception->getMessage(), 'Illegal cross-slice dependency'),
        'Cross-slice failures should name the blocked dependency edge.'
    );
    assertTrue(
        str_contains($exception->getMessage(), 'Consumer slice [checkout] cannot use dependency slice [payments]'),
        'Cross-slice failures should expose the consumer and dependency slices.'
    );
    assertTrue(
        str_contains($exception->getMessage(), 'Likely fix: export the dependency intentionally, import its slice'),
        'Cross-slice failures should suggest an ownership-aware fix.'
    );
}

$runtimeInputContainer = makeTestContainer();
$runtimeInputContainer->bind(StoryRuntimeInputConsumer::class, StoryRuntimeInputConsumer::class);

try {
    $runtimeInputContainer->make(StoryRuntimeInputConsumer::class);
    throw new RuntimeException('Missing runtime input should fail.');
} catch (ContainerException $exception) {
    assertTrue(
        str_contains($exception->getMessage(), 'Runtime input [$token] is missing'),
        'Runtime-input failures should name the missing input.'
    );
    assertTrue(
        str_contains($exception->getMessage(), 'Likely fix: pass an explicit override, use forContext(), or add a default value.'),
        'Runtime-input failures should remain fix-oriented.'
    );
}

$locatorDriftContainer = makeTestContainer();
$locatorDriftContainer->bind(StoryLocatorDrift::class, StoryLocatorDrift::class)
    ->asCapability('checkout')
    ->asShared()
    ->export();

try {
    $locatorDriftContainer->get(StoryLocatorDrift::class);
    throw new RuntimeException('Service locator drift should fail fast.');
} catch (ContainerException $exception) {
    assertTrue(
        str_contains($exception->getMessage(), 'Service locator drift blocked'),
        'Service locator failures should name the anti-pattern explicitly.'
    );
    assertTrue(
        str_contains($exception->getMessage(), 'inject the concrete dependency boundary instead of the container'),
        'Service locator failures should suggest an ownership-safe fix.'
    );
}

echo basename(__FILE__) . " ok\n";
