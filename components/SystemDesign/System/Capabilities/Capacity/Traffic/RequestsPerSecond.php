<?php

declare(strict_types=1);

namespace Avax\Components\SystemDesign\System\Capabilities\Capacity\Traffic;

/**
 * Traffic rate model — requests per second breakdown.
 *
 * @experimental V3 labs
 */
final readonly class RequestsPerSecond
{
    public function __construct(
        public int $total,
        public int $reads,
        public int $writes,
    ) {}

    public function readWriteRatio() : float
    {
        if ($this->writes === 0) {
            return $this->reads > 0 ? INF : 0.0;
        }

        return $this->reads / $this->writes;
    }

    /**
     * @return array{valid: bool, errors: list<string>}
     */
    public function validate() : array
    {
        $errors = [];

        if ($this->total < 0) {
            $errors[] = 'total requests_per_second must be non-negative.';
        }

        if ($this->reads < 0) {
            $errors[] = 'reads_per_second must be non-negative.';
        }

        if ($this->writes < 0) {
            $errors[] = 'writes_per_second must be non-negative.';
        }

        $sum = $this->reads + $this->writes;
        if ($sum !== $this->total) {
            $errors[] = "reads ({$this->reads}) + writes ({$this->writes}) must equal total ({$this->total}).";
        }

        return ['valid' => $errors === [], 'errors' => $errors];
    }
}
