<?php

declare(strict_types=1);

namespace Avax\Components\Presentation\View\System\Flows\RenderView;

use Avax\Components\Presentation\View\System\Capabilities\Engines\TemplateEngineInterface;

final readonly class RenderView
{
    public function __construct(
        private TemplateEngineInterface $engine,
    ) {}

    public function handle(string $view, array $data = []): string
    {
        return $this->engine->render($view, $data);
    }
}
