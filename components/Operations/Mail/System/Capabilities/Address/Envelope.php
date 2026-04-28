<?php

declare(strict_types=1);

namespace Avax\Mail\System\Capabilities\Address;

final class Envelope
{
    public function __construct(
        public readonly string      $from,
        public readonly string|null $returnPath = null,
        public readonly array       $senderOptions = []
    ) {}

    public function withFrom(string $from) : self
    {
        return new self(
            from         : $from,
            returnPath   : $this->returnPath,
            senderOptions: $this->senderOptions
        );
    }

    public function withReturnPath(string $returnPath) : self
    {
        return new self(
            from         : $this->from,
            returnPath   : $returnPath,
            senderOptions: $this->senderOptions
        );
    }
}