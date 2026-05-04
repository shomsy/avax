<?php

declare(strict_types=1);

namespace Avax\Components\DeveloperTools\Dx\System\Capabilities\Templates;

class ProjectTemplate
{
    /**
     * @var array<string, string>
     */
    private array $files = [];

    /**
     * @var array<string, string>
     */
    private array $replacements = [];

    public function __construct(private readonly string $name)
    {
    }

    public function addFile(string $path, string $content): self
    {
        $this->files[$path] = $content;
        return $this;
    }

    public function addReplacement(string $search, string $replace): self
    {
        $this->replacements[$search] = $replace;
        return $this;
    }

    /**
     * @return array<string, string>
     */
    public function render(): array
    {
        $rendered = [];
        foreach ($this->files as $path => $content) {
            $rendered[$path] = str_replace(
                array_keys($this->replacements),
                array_values($this->replacements),
                $content
            );
        }

        return $rendered;
    }

    public function getName(): string
    {
        return $this->name;
    }
}