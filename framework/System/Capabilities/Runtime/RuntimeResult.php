<?php

declare(strict_types=1);

namespace Avax\Framework\System\Capabilities\Runtime;

final readonly class RuntimeResult
{
    private function __construct(
        private ?RuntimeResponse $response,
        private int $exitCode,
        private string $output,
    ) {}

    public static function fromResponse(RuntimeResponse $response) : self
    {
        return new self(
            response: $response,
            exitCode: 0,
            output  : $response->body(),
        );
    }

    public static function fromConsoleOutput(string $output, int $exitCode = 0) : self
    {
        return new self(
            response: null,
            exitCode: $exitCode,
            output  : $output,
        );
    }

    public function response() : ?RuntimeResponse
    {
        return $this->response;
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
