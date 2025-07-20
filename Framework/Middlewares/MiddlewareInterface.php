<?php

declare(strict_types=1);

namespace Gemini\Middlewares;

interface MiddlewareInterface
{
    public function handle(string $commandName, array $arguments, callable $next) : void;
}
