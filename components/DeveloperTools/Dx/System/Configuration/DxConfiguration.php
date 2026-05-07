<?php

declare(strict_types=1);

namespace Avax\Components\DeveloperTools\Dx\System\Configuration;

class DxConfiguration implements DxConfigurationInterface
{
    public function __construct(private readonly bool $verbose = false, private readonly bool $colorEnabled = true, private readonly array $templates = [], private readonly string $defaultTemplate = 'default') {}

    public function isVerbose() : bool
    {
        return $this->verbose;
    }

    public function isColorEnabled() : bool
    {
        return $this->colorEnabled;
    }

    public function getTemplates() : array
    {
        return $this->templates;
    }

    public function getDefaultTemplate() : string
    {
        return $this->defaultTemplate;
    }
}
