<?php

declare(strict_types=1);

namespace Avax\Components\Framework\System\Configuration\ConfigureRuntime;

use Avax\Components\Framework\System\Capabilities\Runtime\RuntimeInterface;

final class RuntimeConfiguration
{
    private string $adapter = 'php-fpm';
    private array $options = [];

    public function setAdapter(string $adapter): self
    {
        $this->adapter = $adapter;

        return $this;
    }

    public function getAdapter(): string
    {
        return $this->adapter;
    }

    public function setOptions(array $options): self
    {
        $this->options = $options;

        return $this;
    }

    public function getOptions(): array
    {
        return $this->options;
    }

    public function createRuntime(): RuntimeInterface
    {
        return match ($this->adapter) {
            'php-fpm' => new \Avax\Components\Framework\System\Capabilities\Runtime\PhpFpm\PhpFpmRuntime(),
            'cli' => new \Avax\Components\Framework\System\Capabilities\Runtime\Cli\CliRuntime(),
            'roadrunner' => new \Avax\Components\Framework\System\Capabilities\Runtime\Adapters\RoadRunner\RoadRunnerRuntime(),
            'frankenphp' => new \Avax\Components\Framework\System\Capabilities\Runtime\Adapters\FrankenPhp\FrankenPhpRuntime(),
            'swoole' => new \Avax\Components\Framework\System\Capabilities\Runtime\Adapters\Swoole\SwooleRuntime(),
            'workerman' => new \Avax\Components\Framework\System\Capabilities\Runtime\Adapters\Workerman\WorkermanRuntime(),
            default => throw new \RuntimeException("Unknown adapter: {$this->adapter}"),
        };
    }
}