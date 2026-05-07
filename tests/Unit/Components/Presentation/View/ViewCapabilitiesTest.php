<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\Presentation\View;

use Avax\Components\Presentation\View\System\Capabilities\Engines\TemplateEngineInterface;
use Avax\Components\Presentation\View\System\Flows\RenderView\RenderView;
use Avax\Components\Presentation\View\System\Foundation\Failure\ViewFailure;
use PHPUnit\Framework\TestCase;
use RuntimeException;

final class ViewCapabilitiesTest extends TestCase
{
    public function test_it_renders_view_through_template_engine() : void
    {
        $engine = new class implements TemplateEngineInterface {
            /** @param array<string, mixed> $data */
            public function render(string $view, array $data = []) : string
            {
                return sprintf('Hello, %s!', $data['name'] ?? 'World');
            }

            public function exists(string $view) : bool
            {
                return true;
            }

            public function share(string $key, mixed $value) : void {}
        };

        $flow   = new RenderView($engine);
        $result = $flow->handle('welcome', ['name' => 'AvaX']);
        $this->assertSame('Hello, AvaX!', $result);
    }

    public function test_it_renders_view_without_data() : void
    {
        $engine = new class implements TemplateEngineInterface {
            /** @param array<string, mixed> $data */
            public function render(string $view, array $data = []) : string
            {
                return 'Static content';
            }

            public function exists(string $view) : bool
            {
                return true;
            }

            public function share(string $key, mixed $value) : void {}
        };

        $flow = new RenderView($engine);
        $this->assertSame('Static content', $flow->handle('page'));
    }

    public function test_view_failure_is_runtime_exception() : void
    {
        $failure = new ViewFailure('Template not found');
        $this->assertInstanceOf(RuntimeException::class, $failure);
        $this->assertSame('Template not found', $failure->getMessage());
    }
}
