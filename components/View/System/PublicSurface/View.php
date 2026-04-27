<?php

declare(strict_types=1);

namespace Avax\Components\View\System\PublicSurface;

final class View implements ViewInterface
{
    public function render(string $template, array $data = []): string
    {
        return "Template: {$template}";
    }
}