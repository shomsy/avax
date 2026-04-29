<?php
declare(strict_types=1);

namespace Avax\Components\CLI\Commands\System\Configuration;

use Avax\Components\CLI\Commands\System\PublicSurface\MakeControllerCommand;
use Avax\Components\CLI\Commands\System\PublicSurface\MakeEntityCommand;

final class CommandRegistry
{
    private array $commands = [];

    public function __construct()
    {
        $this->commands = [
            'make:controller' => MakeControllerCommand::class,
            'make:entity' => MakeEntityCommand::class,
        ];
    }

    public function all(): array { return $this->commands; }
}
