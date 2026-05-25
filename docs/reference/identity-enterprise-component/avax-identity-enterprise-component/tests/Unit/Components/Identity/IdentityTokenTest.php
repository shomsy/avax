<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\Identity;

use Avax\Components\Identity\Configuration\CreateIdentityRuntimeGraph;
use Avax\Components\Identity\Configuration\IdentityConfiguration;
use Avax\Components\Identity\Flows\IssueAccessToken\IssueAccessTokenRequest;
use Avax\Components\Identity\Foundation\Values\TokenSecret;
use Avax\Components\Identity\Foundation\Values\UserId;
use DateInterval;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class IdentityTokenTest extends TestCase
{
    #[Test]
    public function access_token_can_be_issued_and_verified(): void
    {
        $graph = (new CreateIdentityRuntimeGraph())->create(new IdentityConfiguration(TokenSecret::fromString(str_repeat('b', 32))));
        $issued = $graph->tokens()->issueAccessToken()->issue(new IssueAccessTokenRequest(
            userId: UserId::fromString('user-1'),
            ttl: new DateInterval('PT10M'),
            scopes: ['read'],
        ));

        $verified = $graph->tokens()->verifyAccessToken()->verify($issued->token());

        self::assertSame('user-1', $verified->claims()->userId()->toString());
        self::assertSame(['read'], $verified->claims()->scopes());
    }
}
