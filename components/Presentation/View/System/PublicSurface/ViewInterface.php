<?php

declare(strict_types=1);

namespace Avax\Components\Presentation\View\System\PublicSurface;

interface ViewInterface
{
    public function render(string $template, array $data = []) : string;
}