<?php

declare(strict_types=1);

namespace Avax\Framework\System\Configuration\ConfigureRuntime;

use Avax\Framework\System\Capabilities\Runtime\Adapters\FrankenPhp\FrankenPhpRuntime;
use Avax\Framework\System\Capabilities\Runtime\Adapters\RoadRunner\RoadRunnerRuntime;
use Avax\Framework\System\Capabilities\Runtime\Adapters\Swoole\SwooleRuntime;
use Avax\Framework\System\Capabilities\Runtime\Adapters\Workerman\WorkermanRuntime;
use Avax\Framework\System\Capabilities\Runtime\Cli\CliRuntime;
use Avax\Framework\System\Capabilities\Runtime\PhpFpm\PhpFpmRuntime;
use Avax\Framework\System\Capabilities\Runtime\RuntimeInterface;
use RuntimeException;

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
            'php-fpm' => new PhpFpmRuntime(),
            'cli' => new CliRuntime(),
            'roadrunner' => new RoadRunnerRuntime(),
            'frankenphp' => new FrankenPhpRuntime(),
            'swoole' => new SwooleRuntime(),
            'workerman' => new WorkermanRuntime(),
            default => throw new RuntimeException("Unknown adapter: {$this->adapter}"),
        };
    }
}