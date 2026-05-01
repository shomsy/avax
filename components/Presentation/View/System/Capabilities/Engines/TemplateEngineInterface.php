<?php

declare(strict_types=1);

namespace Avax\Components\Presentation\View\System\Capabilities\Engines;

interface TemplateEngineInterface
{
    public function render(string $view, array $data = []): string;

    public function exists(string $view): bool;

    public function share(string $key, mixed $value): void;
}
