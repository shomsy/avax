<?php

declare(strict_types=1);

namespace Avax\Framework\System\Flows\ValidateConfig;

use Avax\Framework\System\Capabilities\ConfigValidation\ConfigSchema;
use Avax\Framework\System\Capabilities\ConfigValidation\ConfigSchemaViolation;
use Avax\Framework\System\Capabilities\ConfigValidation\ConfigValidator;

final readonly class ValidateConfig
{
    public function __construct(
        private ConfigValidator $validator = new ConfigValidator,
    ) {}

    /**
     * @param list<ConfigSchemaViolation> $violations
     */
    public static function printReport(array $violations) : int
    {
        if (empty($violations)) {
            echo "\033[32mConfig validation passed.\033[0m\n";

            return 0;
        }

        $errorCount = 0;
        $warningCount = 0;

        foreach ($violations as $violation) {
            if ($violation->isError()) {
                $errorCount++;
            } else {
                $warningCount++;
            }

            $color = $violation->isError() ? '31' : '33';
            echo sprintf(
                "\033[%sm[%s] %s\033[0m\n",
                $color,
                strtoupper($violation->severity),
                $violation->message,
            );

            if ($violation->remediation !== null) {
                echo sprintf("  Fix: %s\n", $violation->remediation);
            }

            echo "\n";
        }

        echo "Summary: \033[31m{$errorCount} errors\033[0m, \033[33m{$warningCount} warnings\033[0m\n";

        return $errorCount > 0 ? 1 : 0;
    }

    public function registerSchema(ConfigSchema $schema) : self
    {
        $this->validator->register($schema);

        return $this;
    }

    /**
     * @param array<string, array<string, mixed>> $config
     *
     * @return list<ConfigSchemaViolation>
     */
    public function validate(array $config): array
    {
        return $this->validator->validateAll($config);
    }
}
