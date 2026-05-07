<?php

declare(strict_types=1);

namespace Avax\Components\DeveloperTools\Dx\System\Capabilities\Commands;

use Stringable;

class DxCommand implements Stringable
{
    /**
     * @var array<string, string|null>
     */
    private array $options = [];

    /**
     * @var array<string, string>
     */
    private array $arguments = [];

    public function __construct(private readonly string $name) {}

    public function addOption(string $name, ?string $value = null) : self
    {
        $this->options[$name] = $value;

        return $this;
    }

    /**
     * @return array<string, string>
     */
    public function addArgument(string $name, string $value) : self
    {
        $this->arguments[$name] = $value;

        return $this;
    }

    /**
     * @return array{command: string, options: array<string, string|null>, arguments: array<string, string>,
     *                        executed_at: string}
     */
    public function execute() : array
    {
        return [
            'command'     => $this->name,
            'options'     => $this->options,
            'arguments'   => $this->arguments,
            'executed_at' => date('c'),
        ];
    }

    public function __toString() : string
    {
        return $this->name;
    }
}
