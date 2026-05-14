<?php

declare(strict_types=1);

namespace Avax\Framework\System\Flows\AuditContainerScope;

use Avax\Components\Application\Container\System\PublicSurface\ContainerInterface;
use Avax\Framework\System\Capabilities\ContainerIntelligence\ContainerAnalyzer;
use Avax\Framework\System\Capabilities\ContainerIntelligence\ScopeViolation;

final readonly class AuditContainerScope
{
    public function __construct(
        private ContainerInterface $container,
    ) {
    }

    /**
     * @param  list<ScopeViolation>  $violations
     */
    public static function printReport(array $violations): int
    {
        if ($violations === []) {
            echo "\033[32mNo scope violations detected.\033[0m\n";

            return 0;
        }

        foreach ($violations as $violation) {
            echo sprintf(
                "\033[31m[VIOLATION] %s\033[0m\n",
                $violation->message,
            );
            echo sprintf("  Service: %s\n", $violation->service);
            echo sprintf("  Dependency: %s\n\n", $violation->dependency);
        }

        echo 'Total: '.count($violations)." violation(s)\n";

        return 1;
    }

    /**
     * @return list<ScopeViolation>
     */
    public function audit(): array
    {
        $containerAnalyzer = new ContainerAnalyzer($this->container);

        return $containerAnalyzer->detectScopeViolations();
    }
}
