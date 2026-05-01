<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\ApiVersioning\System\Capabilities\Lifecycle;

use DateTimeInterface;
use InvalidArgumentException;

final class VersionRegistry
{
    /** @var array<int, true> */
    private array $supportedVersions;

    /** @var array<int, DateTimeInterface> */
    private array $deprecatedVersions = [];

    /**
     * @param list<int> $supportedVersions
     */
    public function __construct(private int $currentVersion = 1, array $supportedVersions = [1])
    {
        if ($this->currentVersion < 1) {
            throw new InvalidArgumentException(message: 'Current API version must be greater than zero.');
        }

        $this->supportedVersions = [];

        foreach ($supportedVersions as $version) {
            $this->support(version: $version);
        }

        $this->support(version: $this->currentVersion);
    }

    public function support(int $version) : void
    {
        if ($version < 1) {
            throw new InvalidArgumentException(message: 'Supported API version must be greater than zero.');
        }

        $this->supportedVersions[$version] = true;
    }

    public function current() : int
    {
        return $this->currentVersion;
    }

    public function markDeprecated(int $version, DateTimeInterface $sunset) : void
    {
        $this->support(version: $version);
        $this->deprecatedVersions[$version] = $sunset;
    }

    /**
     * @return list<int>
     */
    public function supported() : array
    {
        $versions = array_keys(array: $this->supportedVersions);
        sort(array: $versions);

        return $versions;
    }

    public function deprecated(int $version) : bool
    {
        return isset($this->deprecatedVersions[$version]);
    }

    public function sunset(int $version) : DateTimeInterface|null
    {
        return $this->deprecatedVersions[$version] ?? null;
    }
}
