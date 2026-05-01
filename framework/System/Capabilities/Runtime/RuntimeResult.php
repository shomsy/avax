<?php

declare(strict_types=1);

namespace Avax\Framework\System\Capabilities\Runtime;

final readonly class RuntimeResult
{
    private function __construct(
        private ?RuntimeResponse $runtimeResponse,
        private int $exitCode,
        private string $output,
    ) {}

    public static function fromResponse(RuntimeResponse $runtimeResponse) : self
    {
        return new self(
            runtimeResponse: $runtimeResponse,
            exitCode: 0,
            output  : $runtimeResponse->body(),
        );
    }

    public static function fromConsoleOutput(string $output, int $exitCode = 0) : self
    {
        return new self(
            runtimeResponse: null,
            exitCode: $exitCode,
            output  : $output,
        );
    }

    public function response() : ?RuntimeResponse
    {
        return $this->runtimeResponse;
    }

    public function exitCode() : int
    {
        return $this->exitCode;
    }

    public function output() : string
    {
        return $this->output;
    }
}
