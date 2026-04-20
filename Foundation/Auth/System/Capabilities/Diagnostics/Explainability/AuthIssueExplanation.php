<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capabilities\Diagnostics\Explainability;

use SensitiveParameter;

final readonly class AuthIssueExplanation
{
    /** @var array<string, scalar|null> */
    public array  $context;
    /** @var list<string> */
    public array  $resolution;
    public string $meaning;
    public string $message;
    public string $code;

    /**
     * @param list<string>               $resolution
     * @param array<string, scalar|null> $context
     */
    public function __construct(
        #[SensitiveParameter] string $code,
        string                       $message,
        string                       $meaning,
        array                        $resolution,
        array                        $context = []
    )
    {
        $this->code       = $code;
        $this->message    = $message;
        $this->meaning    = $meaning;
        $this->resolution = $resolution;
        $this->context    = $context;
    }
}
