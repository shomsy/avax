<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Delivery\System\Capabilities\Compile;

class CompileApplication
{
    /**
     * @var array<string, mixed>
     */
    private array $compiled = [];

    public function addCompiled(string $name, mixed $data): self
    {
        $this->compiled[$name] = $data;

        return $this;
    }

    /**
     * @return array{compiled: array<string, mixed>, version: string, compiled_at: string}
     */
    public function compile(): array
    {
        return [
            'compiled' => $this->compiled,
            'version' => '1.0.0',
            'compiled_at' => date('c'),
        ];
    }

    public function warm(): self
    {
        return $this;
    }
}
