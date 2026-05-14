<?php

declare(strict_types=1);

namespace Avax\Framework\System\Foundation\Paths;

use Avax\Framework\System\Foundation\Failure\FrameworkMisconfigured;

final readonly class ProjectPath
{
    private string $value;

    /**
     * @throws FrameworkMisconfigured When path is empty or does not exist
     */
    public function __construct(string $value)
    {
        $normalizedPath = rtrim(string: trim(string: $value), characters: '/');

        if ($normalizedPath === '') {
            throw new FrameworkMisconfigured(message: 'Project path cannot be empty.');
        }

        if (! is_dir(filename: $normalizedPath)) {
            throw new FrameworkMisconfigured(
                message: sprintf('Project path "%s" does not exist.', $normalizedPath),
            );
        }

        $this->value = $normalizedPath;
    }

    public function toString(): string
    {
        return $this->value;
    }

    public function join(string $relativePath): string
    {
        return $this->value.'/'.ltrim(string: $relativePath, characters: '/');
    }
}
