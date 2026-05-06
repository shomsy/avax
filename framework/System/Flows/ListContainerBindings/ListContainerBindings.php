<?php

declare(strict_types=1);

namespace Avax\Framework\System\Flows\ListContainerBindings;

use Avax\Components\Application\Container\System\ContainerInterface;

final readonly class ListContainerBindings
{
    public function __construct(
        private ContainerInterface $container,
    ) {
    }

    /**
     * @param  array<string, array<string, mixed>>  $bindings
     */
    public static function printTable(array $bindings): void
    {
        if ($bindings === []) {
            echo "No bindings registered.\n";

            return;
        }

        $width = 70;
        echo str_repeat('-', $width)."\n";
        echo sprintf("%-50s | %-10s | %s\n", 'Service', 'Scope', 'Shared');
        echo str_repeat('-', $width)."\n";

        foreach ($bindings as $id => $info) {
            $scope = $info['scope'] ?? 'singleton';
            $shared = ($info['shared'] ?? false) ? 'yes' : 'no';

            echo sprintf("%-50s | %-10s | %s\n", $id, $scope, $shared);
        }

        echo str_repeat('-', $width)."\n";
        echo 'Total: '.count($bindings)." bindings\n";
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    public function list(): array
    {
        return $this->container->debugGraph(id: '');
    }
}
