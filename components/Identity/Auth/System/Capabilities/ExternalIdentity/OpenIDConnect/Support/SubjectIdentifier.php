<?php

declare(strict_types=1);

namespace Avax\Components\Auth\System\Capabilities\ExternalIdentity\OpenIDConnect\Support;

/**
 * Manages OIDC subject identifiers (sub claim).
 *
 * Supports both public and pairwise subject identifier generation.
 */
final readonly class SubjectIdentifier
{
    public function __construct(private SubjectIdentifierStrategy $strategy = SubjectIdentifierStrategy::PUBLIC) {}

    /**
     * Generates a subject identifier for a user.
     */
    public function generate(
        string      $localSubject,
        string|null $sectorIdentifier = null,
        string|null $pairwiseSalt = null
    ) : string
    {
        return match ($this->strategy) {
            SubjectIdentifierStrategy::PUBLIC   => $this->publicIdentifier(localSubject: $localSubject),
            SubjectIdentifierStrategy::PAIRWISE => $this->pairwiseIdentifier(
                localSubject    : $localSubject,
                sectorIdentifier: $sectorIdentifier,
                pairwiseSalt    : $pairwiseSalt ?? $this->defaultSalt()
            ),
        };
    }

    private function publicIdentifier(string $localSubject) : string
    {
        return $localSubject;
    }

    private function pairwiseIdentifier(
        string      $localSubject,
        string|null $sectorIdentifier,
        string      $pairwiseSalt
    ) : string
    {
        $sector = $sectorIdentifier ?? $this->defaultSector();

        return $this->hashPairwise(localSubject: $localSubject, sector: $sector, salt: $pairwiseSalt);
    }

    private function defaultSector() : string
    {
        return 'default-sector-identifier';
    }

    private function hashPairwise(string $localSubject, string $sector, string $salt) : string
    {
        $input = $localSubject . '.' . $sector . '.' . $salt;

        return hash(algo: 'sha256', data: $input);
    }

    private function defaultSalt() : string
    {
        return 'default-pairwise-salt';
    }

    /**
     * Validates a sector identifier for pairwise subjects.
     */
    public function isValidSectorIdentifier(string $sectorIdentifier) : bool
    {
        $parsed = parse_url(url: $sectorIdentifier);

        return isset($parsed['scheme']) && isset($parsed['host']);
    }
}
