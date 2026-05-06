<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/bootstrap.php';

use Avax\Components\Application\Container\System\Capabilities\Execution\Injection\Attributes\RuntimeInput;

final class analysis_hints_fixture
{
}

final class HintPipelineStepA
{
}

final class HintPipelineStepB
{
}

final class HintRuntimeInputConsumer
{
    public function __construct(public HintIdentityService $hintIdentityService, #[SensitiveParameter]
        #[RuntimeInput(name: 'token')] public string $token)
    {
    }
}

final class HintConditionalService
{
}

$container = makeTestContainer();

$container->singleton(abstract: HintIdentityService::class, concrete: HintIdentityService::class)
    ->asCapability(ownerSlice: 'capability.identity')
    ->asShared()
    ->export();
$container->bind(abstract: HintPipelineStepA::class, concrete: HintPipelineStepA::class)
    ->asFlow(ownerSlice: 'flow.hints')
    ->asPrivate()
    ->group(group: 'hint.pipeline', order: 10)
    ->entry()
    ->import(slices: 'capability.identity');
$container->bind(abstract: HintPipelineStepB::class, concrete: HintPipelineStepB::class)
    ->asFlow(ownerSlice: 'flow.hints')
    ->asPrivate()
    ->group(group: 'hint.pipeline', order: 20)
    ->import(slices: 'capability.identity');
$container->bind(abstract: HintRuntimeInputConsumer::class, concrete: HintRuntimeInputConsumer::class)
    ->asFlow(ownerSlice: 'flow.hints')
    ->asPrivate()
    ->import(slices: 'capability.identity');
$container->singleton(abstract: HintConditionalService::class, concrete: HintConditionalService::class)
    ->asCapability(ownerSlice: 'capability.identity')
    ->asShared()
    ->export()
    ->profiles(profiles: 'prod')
    ->flags(flags: 'beta');

return $container;
