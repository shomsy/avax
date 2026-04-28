<?php

declare(strict_types=1);

namespace Avax\Components\Presentation\View\System\Flows\RenderView;

use Avax\Components\Presentation\View\System\PublicSurface\View;

final class RenderView
{
    public function render(View $view, string $template, array $data = []) : string
    {
        return $view->render($template, $data);
    }
}