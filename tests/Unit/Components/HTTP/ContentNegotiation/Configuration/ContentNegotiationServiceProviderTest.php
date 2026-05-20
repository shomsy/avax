<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\HTTP\ContentNegotiation\Configuration;

use Avax\Components\HTTP\ContentNegotiation\System\Configuration\ContentNegotiationServiceProvider;
use Avax\Components\HTTP\ContentNegotiation\System\PublicSurface\ContentFormatter;
use Avax\Components\HTTP\ContentNegotiation\System\PublicSurface\CsvFormatter;
use Avax\Components\HTTP\ContentNegotiation\System\PublicSurface\JsonFormatter;
use Avax\Components\HTTP\ContentNegotiation\System\PublicSurface\XmlFormatter;
use Avax\Components\Application\Container\System\Foundation\SimpleContainer;
use PHPUnit\Framework\TestCase;

final class ContentNegotiationServiceProviderTest extends TestCase
{
    private SimpleContainer $container;
    private ContentNegotiationServiceProvider $provider;

    protected function setUp(): void
    {
        $this->container = new SimpleContainer();
        $this->provider = new ContentNegotiationServiceProvider();
        $this->provider->register($this->container);
        $this->provider->boot($this->container);
    }

    public function test_json_formatter_resolves(): void
    {
        $formatter = $this->container->get(JsonFormatter::class);

        $this->assertInstanceOf(JsonFormatter::class, $formatter);
    }

    public function test_xml_formatter_resolves(): void
    {
        $formatter = $this->container->get(XmlFormatter::class);

        $this->assertInstanceOf(XmlFormatter::class, $formatter);
    }

    public function test_csv_formatter_resolves(): void
    {
        $formatter = $this->container->get(CsvFormatter::class);

        $this->assertInstanceOf(CsvFormatter::class, $formatter);
    }

    public function test_content_formatter_interface_resolves(): void
    {
        $formatter = $this->container->get(ContentFormatter::class);

        $this->assertInstanceOf(ContentFormatter::class, $formatter);
        $this->assertInstanceOf(JsonFormatter::class, $formatter);
    }
}
