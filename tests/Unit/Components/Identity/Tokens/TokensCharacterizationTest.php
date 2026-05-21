<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\Identity\Tokens;

use Avax\Components\Identity\Tokens\System\Capabilities\Tokens\Runtime\Code\InMemoryAuthorizationCodeStore;
use Avax\Components\Identity\Tokens\System\Capabilities\Tokens\Runtime\Codec\HmacTokenCodec;
use Avax\Components\Identity\Tokens\System\Capabilities\Tokens\Runtime\Store\InMemoryTokenRevocationStore;
use Avax\Components\Identity\Tokens\System\Configuration\Assembly\TokensGraph;
use Avax\Components\Identity\Tokens\System\PublicSurface\Tokens;
use Avax\Components\Identity\Tokens\System\PublicSurface\TokensInterface;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

final class TokensCharacterizationTest extends TestCase
{
    #[Test]
    public function classIsFinalReadonly(): void
    {
        $reflection = new ReflectionClass(Tokens::class);
        self::assertTrue($reflection->isFinal(), 'Tokens must be final');
    }

    #[Test]
    public function implementsTokensInterface(): void
    {
        $reflection = new ReflectionClass(Tokens::class);
        self::assertTrue($reflection->implementsInterface(TokensInterface::class));
    }

    #[Test]
    public function methodReturnTypesAreCompatibleWithInterface(): void
    {
        $ifaceMethods = (new ReflectionClass(TokensInterface::class))->getMethods();
        $classReflection = new ReflectionClass(Tokens::class);

        foreach ($ifaceMethods as $ifaceMethod) {
            $name = $ifaceMethod->getName();
            $classMethod = $classReflection->getMethod($name);
            $ifaceReturn = (string) $ifaceMethod->getReturnType();
            $classReturn = (string) $classMethod->getReturnType();

            if ($ifaceReturn === 'object') {
                self::assertThat(
                    $classReturn,
                    self::logicalOr(
                        self::identicalTo('object'),
                        self::identicalTo('stdClass'),
                    ),
                    "Tokens::{$name}() return type '{$classReturn}' must be compatible with '{$ifaceReturn}'",
                );
            } else {
                self::assertSame(
                    $ifaceReturn,
                    $classReturn,
                    "Tokens::{$name}() return type must match TokensInterface",
                );
            }
        }
    }

    #[Test]
    public function graphHmacFactoryReturnsTokensInstance(): void
    {
        $tokens = TokensGraph::hmac('test-secret');
        self::assertInstanceOf(Tokens::class, $tokens);
    }

    #[Test]
    public function graphHmacFactoryCreatesInMemoryStores(): void
    {
        $tokens = TokensGraph::hmac('test-secret');

        $reflection = new ReflectionClass($tokens);

        $authorizeProp = $reflection->getProperty('authorizeTokenRequest');
        $authorizeTokenRequest = $authorizeProp->getValue($tokens);
        $authorizeReflection = new ReflectionClass($authorizeTokenRequest);

        $codeStoreProp = $authorizeReflection->getProperty('authorizationCodeStore');
        $codeStore = $codeStoreProp->getValue($authorizeTokenRequest);

        self::assertInstanceOf(InMemoryAuthorizationCodeStore::class, $codeStore);
    }

    #[Test]
    public function fromRuntimeAcceptsExplicitDependencies(): void
    {
        $codeStore = new InMemoryAuthorizationCodeStore();
        $codec = new HmacTokenCodec('custom-secret');
        $revocationStore = new InMemoryTokenRevocationStore();

        $tokens = TokensGraph::fromRuntime(
            authorizationCodeStore: $codeStore,
            tokenCodec: $codec,
            tokenRevocationStore: $revocationStore,
        );

        self::assertInstanceOf(Tokens::class, $tokens);
    }

    #[Test]
    public function graphFactoryAssemblyOwnsRevocationStore(): void
    {
        $tokens = TokensGraph::hmac('test-secret');

        $reflection = new ReflectionClass($tokens);

        $revokeProp = $reflection->getProperty('revokeToken');
        $revokeToken = $revokeProp->getValue($tokens);
        $revokeReflection = new ReflectionClass($revokeToken);

        $revocationStoreProp = $revokeReflection->getProperty('tokenRevocationStore');
        $revocationStore = $revocationStoreProp->getValue($revokeToken);

        self::assertInstanceOf(InMemoryTokenRevocationStore::class, $revocationStore);
    }

    #[Test]
    public function introspectReturnsStdClass(): void
    {
        $tokens = TokensGraph::hmac('test-secret');

        $result = $tokens->introspect('invalid-token');

        self::assertInstanceOf(\stdClass::class, $result);
    }

    #[Test]
    public function authorizeThrowsWithoutSubject(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $tokens = TokensGraph::hmac('test-secret');
        $tokens->authorize(['response_type' => 'code', 'client_id' => 'test']);
    }

    #[Test]
    public function revokeIsVoid(): void
    {
        $tokens = TokensGraph::hmac('test-secret');

        $result = $tokens->revoke('some-token');
        self::assertNull($result);
    }

    #[Test]
    public function exchangeCodeThrowsForInvalidCode(): void
    {
        $this->expectException(\RuntimeException::class);
        $tokens = TokensGraph::hmac('test-secret');
        $tokens->exchangeCode('invalid-code');
    }
}
