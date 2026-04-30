<?php

declare(strict_types=1);

namespace Avax\Framework\System\Foundation\Paths;

use Avax\Framework\System\Foundation\Failure\FrameworkMisconfigured;

final readonly class RuntimePath
{
    private string $value;

    public function __construct(
        string $value,
        ProjectPath $projectPath,
    ) {
        $normalizedPath = rtrim(string: trim(string: $value), characters: '/');

        if ($normalizedPath === '') {
            throw new FrameworkMisconfigured(message: 'Runtime path cannot be empty.');
        }

        if (str_starts_with(haystack: $normalizedPath, needle: '/')) {
            $this->value = $normalizedPath;

            return;
        }

        $this->value = $projectPath->join(relativePath: $normalizedPath);
    }

    public function toString() : string
    {
        return $this->value;
    }

    public function join(string $relativePath) : string
    {
        return $this->value . '/' . ltrim(string: $relativePath, characters: '/');
    }
}
