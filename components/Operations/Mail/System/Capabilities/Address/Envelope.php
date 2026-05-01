<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Mail\System\Capabilities\Address;

final readonly class Envelope
{
    public function __construct(
        public string  $from,
        public ?string $returnPath = null,
        public array   $senderOptions = [],
    ) {}

    public function withFrom(string $from): self
    {
        return new self(
            from         : $from,
            returnPath   : $this->returnPath,
            senderOptions: $this->senderOptions,
        );
    }

    public function withReturnPath(string $returnPath): self
    {
        return new self(
            from         : $this->from,
            returnPath   : $returnPath,
            senderOptions: $this->senderOptions,
        );
    }
}
