<?php

declare(strict_types=1);

namespace Avax\Components\View\System\Flows\RenderView;

final class RenderView
{
    public function render(\Avax\Components\View\System\PublicSurface\View $view, string $template, array $data = []): string
    {
        return $view->render($template, $data);
    }
}