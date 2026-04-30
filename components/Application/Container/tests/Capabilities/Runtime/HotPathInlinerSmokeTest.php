<?php

declare(strict_types=1);

require_once dirname(path: __DIR__, 2) . '/bootstrap.php';

use Avax\Components\Application\Container\DI\Capabilities\Composition\Compilation\CompiledContainer;
use Avax\Components\Application\Container\DI\Capabilities\Resolution\ResolveRequest;
use Avax\Components\Application\Container\DI\Capabilities\Resolution\ServiceResolver;
use Avax\Components\Application\Container\DI\Capabilities\Runtime\HotPathInliner;

final class HotPathInlinerSmokeTest extends CompiledContainer
{
    protected string $fingerprint = 'hot-path-smoke';

    protected array $entries
        = [
            'inline.service' => 'resolveInlineService',
        ];

    public function resolveInlineService(mixed $resolver, ResolveRequest $resolveRequest, array $overrides) : string
    {
        return 'compiled:' . $resolveRequest->serviceId . ':' . ($overrides['suffix'] ?? 'none');
    }
}

$inliner  = new HotPathInliner();
$compiled = new InlineSmokeCompiled();
$resolver = makeTestContainer()->get(id: ServiceResolver::class);

assertTrue(condition: ! $inliner->isAttached(), message: 'HotPathInliner should start detached.');
assertSame(expected: 'no compiled runtime is attached', actual: $inliner->state()['reason'], message: 'Detached hot path should explain why no compiled path is available.');

$inliner->attach(compiled: $compiled);
assertTrue(condition: $inliner->isAttached(), message: 'HotPathInliner should attach a compiled runtime.');
assertTrue(condition: $inliner->has(serviceId: 'inline.service'), message: 'HotPathInliner should expose attached compiled entries.');
assertSame(expected: 1, actual: $inliner->state(serviceId: 'inline.service')['entryCount'], message: 'HotPathInliner should expose attached entry counts.');
assertSame(
    expected: 'compiled:inline.service:smoke',
    actual  : $inliner->resolve(serviceId: 'inline.service', resolver: $resolver, request: new ResolveRequest(serviceId: 'inline.service', overrides: ['suffix' => 'smoke'])),
    message : 'HotPathInliner should dispatch attached compiled methods.',
);
assertSame(
    expected: 'compiled runtime is attached but the requested entry is missing',
    actual  : $inliner->state(serviceId: 'missing.entry')['reason'],
    message : 'HotPathInliner should explain partial compiled misses.',
);

$inliner->detach();
assertTrue(condition: ! $inliner->isAttached(), message: 'HotPathInliner should detach the compiled runtime.');
assertSame(expected: 'no compiled runtime is attached', actual: $inliner->state(serviceId: 'inline.service')['reason'], message: 'Detached hot path should explain the missing compiled runtime.');

echo basename(path: __FILE__) . " ok\n";
