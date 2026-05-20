<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\HTTP\AfterResponse\Configuration;

use Avax\Components\HTTP\AfterResponse\System\Capabilities\Tasks\AfterResponseQueue;
use Avax\Components\HTTP\AfterResponse\System\Configuration\AfterResponseServiceProvider;
use Avax\Components\Application\Container\System\Foundation\SimpleContainer;
use PHPUnit\Framework\TestCase;

final class AfterResponseServiceProviderTest extends TestCase
{
    private SimpleContainer $container;
    private AfterResponseServiceProvider $provider;

    protected function setUp(): void
    {
        $this->container = new SimpleContainer();
        $this->provider = new AfterResponseServiceProvider();
        $this->provider->register($this->container);
        $this->provider->boot($this->container);
    }

    public function test_after_response_queue_resolves(): void
    {
        $queue = $this->container->get(AfterResponseQueue::class);

        $this->assertInstanceOf(AfterResponseQueue::class, $queue);
    }
}
