<?php

declare(strict_types=1);

require_once dirname(path: __DIR__, 3) . '/bootstrap.php';

use Avax\Components\Application\Container\DI\Capabilities\Diagnostics\Errors\ContainerException;
use Avax\Components\Application\Container\DI\Capabilities\Execution\Injection\Attributes\RuntimeInput;
use Avax\Components\Application\Container\DI\ContainerInterface;

interface StoryInternalContract {}

final class StoryInternalService implements StoryInternalContract {}

final class StoryFlowEntry
{
    public function __construct(public StoryInternalContract $storyInternalContract) {}
}

final class StoryFlowLocalService {}

final class RequestOnlyStoryService {}

final class StoryRuntimeInputConsumer
{
    public function __construct(#[SensitiveParameter] #[RuntimeInput(name: 'token')] public string $token) {}
}

final class StoryLocatorDrift
{
    public ContainerInterface $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }
}

$topLevelContainer = makeTestContainer();
$topLevelContainer->bind(abstract: StoryFlowLocalService::class, concrete: StoryFlowLocalService::class)
    ->asFlow(ownerSlice: 'login')
    ->asPrivate();

try {
    $topLevelContainer->get(id: StoryFlowLocalService::class);

    throw new RuntimeException(message: 'Flow-local services should not resolve from the top-level surface.');
} catch (ContainerException $containerException) {
    assertTrue(
        condition: str_contains(haystack: $containerException->getMessage(), needle: 'is not part of the top-level container surface'),
        message  : 'Top-level access failures should explain the surface violation.',
    );
    assertTrue(
        condition: str_contains(haystack: $containerException->getMessage(), needle: 'Likely fix: mark the flow root as entry(), export the shared capability'),
        message  : 'Top-level access failures should suggest an ownership-aware fix.',
    );
}

$scopedContainer = makeTestContainer();
$scopedContainer->scoped(abstract: RequestOnlyStoryService::class, concrete: RequestOnlyStoryService::class)->request();

try {
    $scopedContainer->get(id: RequestOnlyStoryService::class);

    throw new RuntimeException(message: 'ServerRequest-scoped services should require an active request scope.');
} catch (ContainerException $containerException) {
    assertTrue(
        condition: str_contains(haystack: $containerException->getMessage(), needle: 'requires an active [request] scope'),
        message  : 'Scope failures should name the missing required scope.',
    );
    assertTrue(
        condition: str_contains(haystack: $containerException->getMessage(), needle: "openScope('request') before resolving it"),
        message  : 'Scope failures should suggest the exact scope operation to open.',
    );
}

$crossSliceContainer = makeTestContainer();
$crossSliceContainer->singleton(abstract: StoryInternalContract::class, concrete: StoryInternalService::class)
    ->asCapability(ownerSlice: 'payments')
    ->asInternal();
$crossSliceContainer->bind(abstract: StoryFlowEntry::class, concrete: StoryFlowEntry::class)
    ->asFlow(ownerSlice: 'checkout')
    ->asPrivate()
    ->entry();

try {
    $crossSliceContainer->get(id: StoryFlowEntry::class);

    throw new RuntimeException(message: 'Cross-slice internal dependencies should be blocked.');
} catch (ContainerException $containerException) {
    assertTrue(
        condition: str_contains(haystack: $containerException->getMessage(), needle: 'Illegal cross-slice dependency'),
        message  : 'Cross-slice failures should name the blocked dependency edge.',
    );
    assertTrue(
        condition: str_contains(haystack: $containerException->getMessage(), needle: 'Consumer slice [checkout] cannot use dependency slice [payments]'),
        message  : 'Cross-slice failures should expose the consumer and dependency slices.',
    );
    assertTrue(
        condition: str_contains(haystack: $containerException->getMessage(), needle: 'Likely fix: export the dependency intentionally, import its slice'),
        message  : 'Cross-slice failures should suggest an ownership-aware fix.',
    );
}

$runtimeInputContainer = makeTestContainer();
$runtimeInputContainer->bind(abstract: StoryRuntimeInputConsumer::class, concrete: StoryRuntimeInputConsumer::class);

try {
    $runtimeInputContainer->make(abstract: StoryRuntimeInputConsumer::class);

    throw new RuntimeException(message: 'Missing runtime input should fail.');
} catch (ContainerException $containerException) {
    assertTrue(
        condition: str_contains(haystack: $containerException->getMessage(), needle: 'Runtime input [$token] is missing'),
        message  : 'Runtime-input failures should name the missing input.',
    );
    assertTrue(
        condition: str_contains(haystack: $containerException->getMessage(), needle: 'Likely fix: pass an explicit override, use forContext(), or add a default value.'),
        message  : 'Runtime-input failures should remain fix-oriented.',
    );
}

$locatorDriftContainer = makeTestContainer();
$locatorDriftContainer->bind(abstract: StoryLocatorDrift::class, concrete: StoryLocatorDrift::class)
    ->asCapability(ownerSlice: 'checkout')
    ->asShared()
    ->export();

try {
    $locatorDriftContainer->get(id: StoryLocatorDrift::class);

    throw new RuntimeException(message: 'Service locator drift should fail fast.');
} catch (ContainerException $containerException) {
    assertTrue(
        condition: str_contains(haystack: $containerException->getMessage(), needle: 'Service locator drift blocked'),
        message  : 'Service locator failures should name the anti-pattern explicitly.',
    );
    assertTrue(
        condition: str_contains(haystack: $containerException->getMessage(), needle: 'inject the concrete dependency boundary instead of the container'),
        message  : 'Service locator failures should suggest an ownership-safe fix.',
    );
}

echo basename(path: __FILE__) . " ok\n";
