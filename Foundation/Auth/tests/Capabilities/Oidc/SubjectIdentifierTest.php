<?php

declare(strict_types=1);

namespace Avax\Auth\Tests\Capability\Oidc;

use Avax\Auth\System\Capability\Oidc\SubjectIdentifier;
use Avax\Auth\System\Capability\Oidc\SubjectIdentifierStrategy;
use PHPUnit\Framework\TestCase;

final class SubjectIdentifierTest extends TestCase
{
    public function testPublicStrategyReturnsTheLocalSubject() : void
    {
        $identifier = new SubjectIdentifier(strategy: SubjectIdentifierStrategy::PUBLIC);

        $this->assertSame(expected: 'user-123', actual: $identifier->generate(localSubject: 'user-123'));
    }

    public function testPairwiseStrategyReturnsStablePseudonymousSubject() : void
    {
        $identifier = new SubjectIdentifier(strategy: SubjectIdentifierStrategy::PAIRWISE);

        $first = $identifier->generate(
            localSubject    : 'user-123',
            sectorIdentifier: 'https://rp.example.test',
            pairwiseSalt    : 'salt-1'
        );
        $second = $identifier->generate(
            localSubject    : 'user-123',
            sectorIdentifier: 'https://rp.example.test',
            pairwiseSalt    : 'salt-1'
        );
        $different = $identifier->generate(
            localSubject    : 'user-123',
            sectorIdentifier: 'https://other.example.test',
            pairwiseSalt    : 'salt-1'
        );

        $this->assertSame(expected: $first, actual: $second);
        $this->assertNotSame(expected: $first, actual: $different);
        $this->assertSame(expected: 64, actual: strlen($first));
    }

    public function testSectorIdentifierValidationAcceptsUrisAndRejectsPlainStrings() : void
    {
        $identifier = new SubjectIdentifier(strategy: SubjectIdentifierStrategy::PAIRWISE);

        $this->assertTrue(condition: $identifier->isValidSectorIdentifier('https://rp.example.test'));
        $this->assertFalse(condition: $identifier->isValidSectorIdentifier('not-a-sector'));
    }
}
