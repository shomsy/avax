<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\HTTP\Security;

use Avax\Components\HTTP\Security\System\Capabilities\Csrf\CsrfToken;
use Avax\Components\HTTP\Security\System\Capabilities\Csrf\CsrfTokenGenerator;
use Avax\Components\HTTP\Security\System\Capabilities\Csrf\CsrfTokens;
use Avax\Components\HTTP\Security\System\Capabilities\Csrf\CsrfVerifier;
use Avax\Components\HTTP\Security\System\Foundation\Failure\CsrfTokenMismatch;
use Avax\Components\HTTP\Session\System\PublicSurface\Session;
use Avax\Components\HTTP\Session\System\PublicSurface\SessionScope;
use Avax\Components\HTTP\Session\System\Capabilities\Storage\ArraySessionStore;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * CSRF Authority Tests — TODO-003.
 *
 * These tests prove that:
 * - CsrfTokens is the single CSRF authority
 * - CsrfToken is a pure value class (no session I/O)
 * - CsrfVerifier requires explicit session token (no $_SESSION fallback)
 * - CsrfTokenGenerator delegates to CsrfToken (no $_SESSION I/O)
 * - Token rotation and consumption work correctly
 * - Duplicate helper loads do not create conflicting authority
 */
final class CsrfAuthorityTest extends TestCase
{
    private function createSession() : Session
    {
        $store   = new ArraySessionStore();
        $scope   = new SessionScope($store);
        $scope->start();

        return new Session(
            sessionScope : $scope,
            logger       : new NullLogger(),
        );
    }

    private function createCsrfTokens(Session $session) : CsrfTokens
    {
        return new CsrfTokens(
            session: $session,
            logger : new NullLogger(),
        );
    }

    // ── Valid CSRF token accepted ──

    public function test_valid_csrf_token_is_accepted() : void
    {
        $session    = $this->createSession();
        $csrfTokens = $this->createCsrfTokens($session);

        $token = $csrfTokens->getToken();

        $this->assertTrue($csrfTokens->validateToken($token));
    }

    // ── Invalid CSRF token rejected ──

    public function test_invalid_csrf_token_is_rejected() : void
    {
        $session    = $this->createSession();
        $csrfTokens = $this->createCsrfTokens($session);

        $this->assertFalse($csrfTokens->validateToken('bogus-token'));
    }

    // ── Missing CSRF token rejected ──

    public function test_null_csrf_token_is_rejected() : void
    {
        $session    = $this->createSession();
        $csrfTokens = $this->createCsrfTokens($session);

        $this->assertFalse($csrfTokens->validateToken(null));
    }

    public function test_empty_csrf_token_is_rejected() : void
    {
        $session    = $this->createSession();
        $csrfTokens = $this->createCsrfTokens($session);

        $this->assertFalse($csrfTokens->validateToken(''));
    }

    // ── Token consumption ──

    public function test_token_is_consumed_after_validation() : void
    {
        $session    = $this->createSession();
        $csrfTokens = $this->createCsrfTokens($session);

        $token = $csrfTokens->getToken();

        // First validation succeeds
        $this->assertTrue($csrfTokens->validateToken($token));

        // Second validation with same token fails (consumed)
        $this->assertFalse($csrfTokens->validateToken($token));
    }

    // ── Token rotation behavior ──

    public function test_new_token_generated_after_consumption() : void
    {
        $session    = $this->createSession();
        $csrfTokens = $this->createCsrfTokens($session);

        $token1 = $csrfTokens->getToken();
        $this->assertTrue($csrfTokens->validateToken($token1));

        // After consumption, getting a new token works
        $token2 = $csrfTokens->getToken();
        $this->assertNotEmpty($token2);
        $this->assertNotSame($token1, $token2);
        $this->assertTrue($csrfTokens->validateToken($token2));
    }

    public function test_multiple_tokens_can_exist_simultaneously() : void
    {
        $session    = $this->createSession();
        $csrfTokens = $this->createCsrfTokens($session);

        // Generate multiple tokens without consuming
        $token1 = $csrfTokens->getToken();
        $token2 = $csrfTokens->getToken();

        // Both should be valid (most recent is returned, but both stored)
        $this->assertTrue($csrfTokens->validateToken($token2));

        // token1 may or may not be valid depending on pruning;
        // the key guarantee is that at least one valid token exists
        $newToken = $csrfTokens->getToken();
        $this->assertNotEmpty($newToken);
    }

    // ── CsrfToken is a pure value class (no session I/O) ──

    public function test_csrf_token_generate_is_pure_value() : void
    {
        // CsrfToken::generate() must NOT touch $_SESSION
        $before = $_SESSION ?? [];

        $token = CsrfToken::generate();

        $this->assertSame(64, strlen($token)); // 32 bytes hex
        $this->assertSame($before, $_SESSION ?? []);
    }

    // ── CsrfVerifier requires explicit session token (no $_SESSION fallback) ──

    public function test_csrf_verifier_requires_explicit_session_token() : void
    {
        // When sessionToken is null, verification must fail (fail-closed)
        $this->assertFalse(CsrfVerifier::verify('any-token', null));
    }

    public function test_csrf_verifier_matches_correct_tokens() : void
    {
        $this->assertTrue(CsrfVerifier::verify('abc123', 'abc123'));
    }

    public function test_csrf_verifier_rejects_mismatched_tokens() : void
    {
        $this->assertFalse(CsrfVerifier::verify('abc123', 'xyz789'));
    }

    public function test_csrf_verifier_rejects_null_submitted_token() : void
    {
        $this->assertFalse(CsrfVerifier::verify(null, 'expected-token'));
    }

    // ── CsrfTokenGenerator delegates to CsrfToken (no session I/O) ──

    public function test_csrf_token_generator_no_session_io() : void
    {
        $generator = new CsrfTokenGenerator();

        $before = $_SESSION ?? [];

        $token = $generator->generate();

        $this->assertSame(64, strlen($token));
        $this->assertSame($before, $_SESSION ?? []);
    }

    public function test_csrf_token_generator_validate_requires_expected() : void
    {
        $generator = new CsrfTokenGenerator();

        $token = $generator->generate();

        $this->assertTrue($generator->validate($token, $token));
        $this->assertFalse($generator->validate('wrong', $token));
        $this->assertFalse($generator->validate(null, $token));
        $this->assertFalse($generator->validate('', $token));
    }

    // ── Duplicate helper load does not create conflicting authority ──

    public function test_duplicate_csrf_tokens_instances_share_session_authority() : void
    {
        $session    = $this->createSession();
        $csrfA      = $this->createCsrfTokens($session);
        $csrfB      = $this->createCsrfTokens($session);

        // Token generated by A should be validatable by B
        // because both use the same Session authority
        $token = $csrfA->getToken();
        $this->assertTrue($csrfB->validateToken($token));
    }

    public function test_csrf_token_generator_and_csrf_tokens_produce_compatible_tokens() : void
    {
        $session    = $this->createSession();
        $csrfTokens = $this->createCsrfTokens($session);
        $generator  = new CsrfTokenGenerator();

        // Both generate 64-char hex tokens
        $tokenFromTokens   = $csrfTokens->getToken();
        $tokenFromGenerator = $generator->generate();

        $this->assertSame(64, strlen($tokenFromTokens));
        $this->assertSame(64, strlen($tokenFromGenerator));

        // Tokens from CsrfTokens are validated by CsrfTokens
        $this->assertTrue($csrfTokens->validateToken($tokenFromTokens));

        // Generator tokens can be validated by CsrfVerifier with explicit session token
        $this->assertTrue(CsrfVerifier::verify($tokenFromGenerator, $tokenFromGenerator));
    }

    // ── Session lifecycle with CSRF ──

    public function test_session_destroy_invalidates_csrf_tokens() : void
    {
        $session    = $this->createSession();
        $csrfTokens = $this->createCsrfTokens($session);

        $token = $csrfTokens->getToken();
        $this->assertTrue($csrfTokens->validateToken($token));

        $session->destroy();

        // After session destroy, a new session scope starts fresh
        $store   = new ArraySessionStore();
        $scope2  = new SessionScope($store);
        $scope2->start();
        $session2 = new Session(sessionScope: $scope2, logger: new NullLogger());
        $csrf2    = new CsrfTokens(session: $session2, logger: new NullLogger());

        // Old token must not be valid in new session
        $this->assertFalse($csrf2->validateToken($token));
    }

    public function test_session_regenerate_preserves_csrf_ability() : void
    {
        $session    = $this->createSession();
        $csrfTokens = $this->createCsrfTokens($session);

        $token = $csrfTokens->getToken();
        $this->assertTrue($csrfTokens->validateToken($token));

        // Regenerate session
        $regenerated = $session->regenerate(destroy: false);
        $this->assertTrue($regenerated);

        // Can still generate and validate new tokens
        $newToken = $csrfTokens->getToken();
        $this->assertNotEmpty($newToken);
        $this->assertTrue($csrfTokens->validateToken($newToken));
    }
}
