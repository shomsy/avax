<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/bootstrap.php';

use Avax\Container\DI\Capabilities\Execution\Injection\Attributes\RuntimeInput;

final class HintIdentityService
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
    public function __construct(
        public HintIdentityService $identity,
        #[RuntimeInput('token')] public string $token
    ) {
    }
}

final class HintConditionalService
{
}

$container = makeTestContainer();

$container->singleton(HintIdentityService::class, HintIdentityService::class)
    ->asCapability('capability.identity')
    ->asShared()
    ->export();
$container->bind(HintPipelineStepA::class, HintPipelineStepA::class)
    ->asFlow('flow.hints')
    ->asPrivate()
    ->group('hint.pipeline', 10)
    ->entry()
    ->import('capability.identity');
$container->bind(HintPipelineStepB::class, HintPipelineStepB::class)
    ->asFlow('flow.hints')
    ->asPrivate()
    ->group('hint.pipeline', 20)
    ->import('capability.identity');
$container->bind(HintRuntimeInputConsumer::class, HintRuntimeInputConsumer::class)
    ->asFlow('flow.hints')
    ->asPrivate()
    ->import('capability.identity');
$container->singleton(HintConditionalService::class, HintConditionalService::class)
    ->asCapability('capability.identity')
    ->asShared()
    ->export()
    ->profiles('prod')
    ->flags('beta');

return $container;
