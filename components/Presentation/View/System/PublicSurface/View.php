<?php

declare(strict_types=1);

namespace Avax\Components\Presentation\View\System\PublicSurface;

use Avax\Components\Presentation\View\System\Capabilities\Engines\TemplateEngineInterface;
use Avax\Components\Presentation\View\System\Flows\RenderView\RenderView;

final readonly class View implements ViewInterface
{
    public function __construct(
        private TemplateEngineInterface $templateEngine,
        private RenderView              $renderView,
    ) {}

    public function render(string $view, array $data = []): string
    {
        return $this->renderView->handle($view, $data);
    }

    public function exists(string $view): bool
    {
        return $this->templateEngine->exists($view);
    }

    public function share(string $key, mixed $value): void
    {
        $this->templateEngine->share($key, $value);
    }
}
