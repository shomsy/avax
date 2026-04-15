<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capability\Oidc;

/**
 * Manages OIDC subject identifiers (sub claim).
 *
 * Supports both public and pairwise subject identifier generation.
 */
final readonly class SubjectIdentifier
{
    private SubjectIdentifierStrategy $strategy;

    public function __construct(
        SubjectIdentifierStrategy $strategy = SubjectIdentifierStrategy::PUBLIC
    )
    {
        $this->strategy = $strategy;
    }

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

        return hash('sha256', $input);
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
        $parsed = parse_url($sectorIdentifier);

        return isset($parsed['scheme']) && isset($parsed['host']);
    }
}
