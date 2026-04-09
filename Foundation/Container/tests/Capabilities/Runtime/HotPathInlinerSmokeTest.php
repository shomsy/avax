<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/bootstrap.php';

use Avax\Container\DI\Capabilities\Composition\Compilation\CompiledContainer;
use Avax\Container\DI\Capabilities\Resolution\ResolveRequest;
use Avax\Container\DI\Capabilities\Resolution\ServiceResolver;
use Avax\Container\DI\Capabilities\Runtime\HotPathInliner;

final class InlineSmokeCompiled extends CompiledContainer
{
    protected string $fingerprint = 'hot-path-smoke';

    protected array $entries = [
        'inline.service' => 'resolveInlineService',
    ];

    public function resolveInlineService(mixed $resolver, ResolveRequest $request, array $overrides) : string
    {
        return 'compiled:' . $request->serviceId . ':' . ($overrides['suffix'] ?? 'none');
    }
}

$inliner = new HotPathInliner();
$compiled = new InlineSmokeCompiled();
$resolver = makeTestContainer()->get(ServiceResolver::class);

assertTrue(! $inliner->isAttached(), 'HotPathInliner should start detached.');
assertSame('no compiled runtime is attached', $inliner->state()['reason'], 'Detached hot path should explain why no compiled path is available.');

$inliner->attach($compiled);
assertTrue($inliner->isAttached(), 'HotPathInliner should attach a compiled runtime.');
assertTrue($inliner->has('inline.service'), 'HotPathInliner should expose attached compiled entries.');
assertSame(1, $inliner->state('inline.service')['entryCount'], 'HotPathInliner should expose attached entry counts.');
assertSame(
    'compiled:inline.service:smoke',
    $inliner->resolve('inline.service', resolver: $resolver, request: new ResolveRequest('inline.service', ['suffix' => 'smoke'])),
    'HotPathInliner should dispatch attached compiled methods.'
);
assertSame(
    'compiled runtime is attached but the requested entry is missing',
    $inliner->state('missing.entry')['reason'],
    'HotPathInliner should explain partial compiled misses.'
);

$inliner->detach();
assertTrue(! $inliner->isAttached(), 'HotPathInliner should detach the compiled runtime.');
assertSame('no compiled runtime is attached', $inliner->state('inline.service')['reason'], 'Detached hot path should explain the missing compiled runtime.');

echo basename(__FILE__) . " ok\n";
