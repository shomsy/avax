<?php

declare(strict_types=1);

namespace Avax\Components\Presentation\View\System\Capabilities\Templates;

interface TemplateCompiler
{
    public function compile(string $template): string;
}
