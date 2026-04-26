<?php

declare(strict_types=1);

namespace components\DataFoundation\DataTransfer\Capabilities\ErrorReporting;

final readonly class ExplainDataTransferFailure
{
    public function explain(DataTransferViolations $violations, string $fallback = 'Data transfer failed.') : string
    {
        if ($violations->isEmpty()) {
            return $fallback;
        }

        $lines = [$fallback];

        foreach ($violations as $violation) {
            $lines[] = sprintf('%s: %s', $violation->path, $violation->message);
        }

        return implode(separator: "\n", array: $lines);
    }
}
