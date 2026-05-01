<?php

declare(strict_types=1);

namespace Avax\Framework\System\Configuration\ConfigureRuntime;

use Avax\Framework\System\Capabilities\Runtime\Adapters\FrankenPhp\FrankenPhpRuntime;
use Avax\Framework\System\Capabilities\Runtime\Adapters\RoadRunner\RoadRunnerRuntime;
use Avax\Framework\System\Capabilities\Runtime\Adapters\Swoole\SwooleRuntime;
use Avax\Framework\System\Capabilities\Runtime\Adapters\Workerman\WorkermanRuntime;
use Avax\Framework\System\Capabilities\Runtime\Cli\CliRuntime;
use Avax\Framework\System\Capabilities\Runtime\PhpFpm\PhpFpmRuntime;
use Avax\Framework\System\Capabilities\Runtime\Worker\WorkerRequest;
use Avax\Framework\System\Capabilities\Runtime\Worker\WorkerResponse;
use Avax\Framework\System\PublicSurface\Console\ConsoleKernelInterface;
use Avax\Framework\System\PublicSurface\Http\HttpKernelInterface;
use Closure;
use RuntimeException;

final class RuntimeConfiguration
{
    private string $adapter = 'php-fpm';

    /**
     * @var array<string, mixed>
     */
    private array $options = [];

    public function setAdapter(string $adapter) : self
    {
        $this->adapter = $adapter;

        return $this;
    }

    public function getAdapter() : string
    {
        return $this->adapter;
    }

    /**
     * @param array<string, mixed> $options
     */
    public function setOptions(array $options) : self
    {
        $this->options = $options;

        return $this;
    }

    /**
     * @return array<string, mixed>
     */
    public function getOptions() : array
    {
        return $this->options;
    }

    public function createRuntime() : object
    {
        return match ($this->adapter) {
            'php-fpm'   => new PhpFpmRuntime(httpKernel: $this->httpKernel()),
            'cli'       => new CliRuntime(consoleKernel: $this->consoleKernel()),
            'roadrunner' => new RoadRunnerRuntime(receiver: $this->receiver(), sender: $this->sender()),
            'frankenphp' => new FrankenPhpRuntime(receiver: $this->receiver(), sender: $this->sender()),
            'swoole'    => new SwooleRuntime(receiver: $this->receiver(), sender: $this->sender()),
            'workerman' => new WorkermanRuntime(receiver: $this->receiver(), sender: $this->sender()),
            default => throw new RuntimeException("Unknown adapter: {$this->adapter}"),
        };
    }

    private function httpKernel() : HttpKernelInterface
    {
        $httpKernel = $this->options['httpKernel'] ?? null;

        if (! $httpKernel instanceof HttpKernelInterface) {
            throw new RuntimeException('The php-fpm runtime requires an httpKernel option.');
        }

        return $httpKernel;
    }

    private function consoleKernel() : ConsoleKernelInterface
    {
        $consoleKernel = $this->options['consoleKernel'] ?? null;

        if (! $consoleKernel instanceof ConsoleKernelInterface) {
            throw new RuntimeException('The cli runtime requires a consoleKernel option.');
        }

        return $consoleKernel;
    }

    /**
     * @return Closure(): (WorkerRequest|null)
     */
    private function receiver() : Closure
    {
        $receiver = $this->options['receiver'] ?? null;

        if ($receiver instanceof Closure) {
            return $receiver;
        }

        if (is_callable($receiver)) {
            return Closure::fromCallable($receiver);
        }

        return static fn () : ?WorkerRequest => null;
    }

    /**
     * @return Closure(WorkerResponse): void
     */
    private function sender() : Closure
    {
        $sender = $this->options['sender'] ?? null;

        if ($sender instanceof Closure) {
            return $sender;
        }

        if (is_callable($sender)) {
            return Closure::fromCallable($sender);
        }

        return static function (WorkerResponse $response) : void {};
    }
}
