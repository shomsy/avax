<?php

declare(strict_types=1);

namespace Tests\Unit\Framework\V4SecurityPolicy;

use Avax\Framework\System\Capabilities\Doctor\Types\DoctorSeverity;
use Avax\Framework\System\Capabilities\Security\Doctor\CheckSecurityRuntime;
use Avax\Framework\System\Capabilities\Security\FeatureFlags\EvaluateFeatureFlag;
use Avax\Framework\System\Capabilities\Security\FeatureFlags\FeatureFlagStore;
use Avax\Framework\System\Capabilities\Security\FeatureFlags\Foundation\FeatureFlag;
use Avax\Framework\System\Capabilities\Security\FeatureFlags\Foundation\FeatureFlagName;
use Avax\Framework\System\Capabilities\Security\FeatureFlags\Foundation\FeatureFlagState;
use Avax\Framework\System\Capabilities\Security\FeatureFlags\InMemoryFeatureFlagStore;
use Avax\Framework\System\Capabilities\Security\PolicyEngine\DefinePolicy;
use Avax\Framework\System\Capabilities\Security\PolicyEngine\EvaluatePolicy;
use Avax\Framework\System\Capabilities\Security\PolicyEngine\Foundation\PolicyAction;
use Avax\Framework\System\Capabilities\Security\PolicyEngine\Foundation\PolicyContext;
use Avax\Framework\System\Capabilities\Security\PolicyEngine\Foundation\PolicyDecision;
use Avax\Framework\System\Capabilities\Security\PolicyEngine\Foundation\PolicyEffect;
use Avax\Framework\System\Capabilities\Security\PolicyEngine\Foundation\PolicyFailure;
use Avax\Framework\System\Capabilities\Security\PolicyEngine\Foundation\PolicyResource;
use Avax\Framework\System\Capabilities\Security\PolicyEngine\Foundation\PolicySubject;
use Avax\Framework\System\Capabilities\Security\PolicyEngine\PolicyDeniedException;
use Avax\Framework\System\Capabilities\Security\RequestSigning\CanonicalizeSignedRequest;
use Avax\Framework\System\Capabilities\Security\RequestSigning\Foundation\NonceStore;
use Avax\Framework\System\Capabilities\Security\RequestSigning\Foundation\SignatureKeyId;
use Avax\Framework\System\Capabilities\Security\RequestSigning\Foundation\SignatureNonce;
use Avax\Framework\System\Capabilities\Security\RequestSigning\Foundation\SignaturePayload;
use Avax\Framework\System\Capabilities\Security\RequestSigning\Foundation\SignatureTimestamp;
use Avax\Framework\System\Capabilities\Security\RequestSigning\RejectExpiredSignature;
use Avax\Framework\System\Capabilities\Security\RequestSigning\RejectReplayedNonce;
use Avax\Framework\System\Capabilities\Security\RequestSigning\SignatureVerificationResult;
use Avax\Framework\System\Capabilities\Security\RequestSigning\SignInternalRequest;
use Avax\Framework\System\Capabilities\Security\RequestSigning\VerifyInternalRequestSignature;
use Avax\Framework\System\Capabilities\Security\ServiceDiscovery\Foundation\ServiceEndpoint;
use Avax\Framework\System\Capabilities\Security\ServiceDiscovery\Foundation\ServiceName;
use Avax\Framework\System\Capabilities\Security\ServiceDiscovery\InMemoryServiceRegistry;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class V4SecurityPolicyTest extends TestCase
{
    // ===== Request Signing Tests =====

    #[Test]
    public function signed_request_verifies_successfully(): void
    {
        $secret = 'test-secret-key-123';
        $signer = new SignInternalRequest($secret, new SignatureKeyId('key-1'));

        $payload = $signer->sign('POST', '/api/users', '{"name":"test"}');

        self::assertNotEmpty($payload->signature);
        self::assertSame('key-1', $payload->keyId->value);
    }

    #[Test]
    public function verify_accepts_valid_signature(): void
    {
        $secret = 'test-secret-key-123';
        $signer = new SignInternalRequest($secret, new SignatureKeyId('key-1'));
        $nonce = SignatureNonce::generate();
        $timestamp = SignatureTimestamp::now();

        $payload = $signer->sign('POST', '/api/users', '{"name":"test"}', [], $nonce, $timestamp);

        $verifier = new VerifyInternalRequestSignature($secret, 300, new NonceStore(), $timestamp->epochSeconds);
        $headers = $payload->toHeaders();

        $result = $verifier->verify('POST', '/api/users', '{"name":"test"}', $headers);

        self::assertTrue($result->valid);
        self::assertSame('', $result->reason);
    }

    #[Test]
    public function tampered_request_fails_verification(): void
    {
        $secret = 'test-secret-key-123';
        $signer = new SignInternalRequest($secret, new SignatureKeyId('key-1'));
        $nonce = SignatureNonce::generate();
        $timestamp = SignatureTimestamp::now();

        $payload = $signer->sign('POST', '/api/users', '{"name":"test"}', [], $nonce, $timestamp);

        $verifier = new VerifyInternalRequestSignature($secret, 300, new NonceStore(), $timestamp->epochSeconds);
        $headers = $payload->toHeaders();

        // Tamper with body
        $result = $verifier->verify('POST', '/api/users', '{"name":"hacked"}', $headers);

        self::assertFalse($result->valid);
        self::assertStringContainsString('Signature mismatch', $result->reason);
    }

    #[Test]
    public function expired_signature_fails(): void
    {
        $secret = 'test-secret-key-123';
        $signer = new SignInternalRequest($secret, new SignatureKeyId('key-1'));
        $nonce = SignatureNonce::generate();
        $oldTimestamp = new SignatureTimestamp(time() - 600);

        $payload = $signer->sign('GET', '/api/health', '', [], $nonce, $oldTimestamp);

        $verifier = new VerifyInternalRequestSignature($secret, 300, new NonceStore(), time());
        $headers = $payload->toHeaders();

        $result = $verifier->verify('GET', '/api/health', '', $headers);

        self::assertFalse($result->valid);
        self::assertStringContainsString('expired', $result->reason);
    }

    #[Test]
    public function replay_nonce_fails(): void
    {
        $secret = 'test-secret-key-123';
        $signer = new SignInternalRequest($secret, new SignatureKeyId('key-1'));
        $nonce = SignatureNonce::generate();
        $timestamp = SignatureTimestamp::now();
        $nonceStore = new NonceStore();

        $payload = $signer->sign('GET', '/api/data', '', [], $nonce, $timestamp);
        $verifier = new VerifyInternalRequestSignature($secret, 300, $nonceStore, $timestamp->epochSeconds);
        $headers = $payload->toHeaders();

        // First verification should succeed
        $result1 = $verifier->verify('GET', '/api/data', '', $headers);
        self::assertTrue($result1->valid);

        // Second verification with same nonce should fail (replay)
        $result2 = $verifier->verify('GET', '/api/data', '', $headers);
        self::assertFalse($result2->valid);
        self::assertStringContainsString('replay', $result2->reason);
    }

    #[Test]
    public function missing_signature_headers_fail(): void
    {
        $verifier = new VerifyInternalRequestSignature('secret', 300, new NonceStore());

        $result = $verifier->verify('GET', '/api/test', '', []);

        self::assertFalse($result->valid);
        self::assertStringContainsString('Missing', $result->reason);
    }

    #[Test]
    public function canonical_request_is_deterministic(): void
    {
        $canonical1 = CanonicalizeSignedRequest::canonical('POST', '/api/users', '{"a":1}', ['content-type' => 'application/json']);
        $canonical2 = CanonicalizeSignedRequest::canonical('POST', '/api/users', '{"a":1}', ['content-type' => 'application/json']);

        self::assertSame($canonical1, $canonical2);
    }

    #[Test]
    public function canonical_path_normalizes_correctly(): void
    {
        $canonical = CanonicalizeSignedRequest::canonical('GET', '/api/../api/users', '');

        self::assertStringContainsString('/api/users', $canonical);
    }

    #[Test]
    public function signature_payload_roundtrips_from_headers(): void
    {
        $payload = new SignaturePayload(
            new SignatureKeyId('key-abc'),
            new SignatureTimestamp(1234567890),
            new SignatureNonce('abc123nonce'),
            'sig-hash-value',
        );

        $headers = $payload->toHeaders();
        $restored = SignaturePayload::fromHeaders($headers);

        self::assertSame($payload->keyId->value, $restored->keyId->value);
        self::assertSame($payload->timestamp->epochSeconds, $restored->timestamp->epochSeconds);
        self::assertSame($payload->nonce->value, $restored->nonce->value);
        self::assertSame($payload->signature, $restored->signature);
    }

    #[Test]
    public function nonce_store_prunes_old_entries(): void
    {
        $store = new NonceStore();
        $store->store('nonce-1', 1000);
        $store->store('nonce-2', 2000);

        self::assertTrue($store->has('nonce-1'));

        $store->pruneOlderThan(1500);

        self::assertFalse($store->has('nonce-1'));
        self::assertTrue($store->has('nonce-2'));
    }

    // ===== Policy Engine Tests =====

    #[Test]
    public function default_deny_when_no_rules_match(): void
    {
        $policy = (new DefinePolicy('test'))->allow('rule-1', static fn () => false);

        $evaluator = new EvaluatePolicy();
        $decision = $evaluator->evaluate(
            $policy,
            new PolicySubject('user-1'),
            new PolicyAction('read'),
            new PolicyResource('document', 'doc-1'),
        );

        self::assertFalse($decision->isAllowed());
        self::assertSame(PolicyEffect::Deny, $decision->effect);
    }

    #[Test]
    public function allow_policy_decision(): void
    {
        $policy = (new DefinePolicy('access'))
            ->allow('admin-all', static fn (
                PolicySubject $subject,
                PolicyAction $action,
                PolicyResource $resource,
                PolicyContext $context,
            ) => $subject->attributes['role'] === 'admin');

        $evaluator = new EvaluatePolicy();
        $decision = $evaluator->evaluate(
            $policy,
            new PolicySubject('admin-1', 'user', ['role' => 'admin']),
            new PolicyAction('delete'),
            new PolicyResource('user', 'user-2'),
        );

        self::assertTrue($decision->isAllowed());
    }

    #[Test]
    public function deny_policy_decision(): void
    {
        $policy = (new DefinePolicy('access'))
            ->deny('no-delete', static fn (
                PolicySubject $subject,
                PolicyAction $action,
                PolicyResource $resource,
                PolicyContext $context,
            ) => $action->name === 'delete');

        $evaluator = new EvaluatePolicy();
        $decision = $evaluator->evaluate(
            $policy,
            new PolicySubject('user-1'),
            new PolicyAction('delete'),
            new PolicyResource('document', 'doc-1'),
        );

        self::assertFalse($decision->isAllowed());
        self::assertStringContainsString('denies', $decision->reason);
    }

    #[Test]
    public function policy_denied_exception_thrown(): void
    {
        $policy = (new DefinePolicy('access'))->deny('no-access');

        $evaluator = new EvaluatePolicy();

        $this->expectException(PolicyDeniedException::class);
        $this->expectExceptionCode(403);

        $evaluator->evaluateOrFail(
            $policy,
            new PolicySubject('user-1'),
            new PolicyAction('read'),
            new PolicyResource('document', 'doc-1'),
        );
    }

    #[Test]
    public function policy_failure_message_is_descriptive(): void
    {
        $failure = new PolicyFailure('user-1', 'delete', 'document:doc-1', 'Rule no-delete denies.');

        self::assertStringContainsString('user-1', $failure->message());
        self::assertStringContainsString('delete', $failure->message());
        self::assertStringContainsString('document:doc-1', $failure->message());
    }

    // ===== Feature Flags Tests =====

    #[Test]
    public function feature_flag_enabled_when_set(): void
    {
        $store = new InMemoryFeatureFlagStore();
        $store->set(new FeatureFlag(
            new FeatureFlagName('dark-mode'),
            FeatureFlagState::Enabled,
        ));

        $evaluator = new EvaluateFeatureFlag($store);

        self::assertTrue($evaluator->isEnabled(new FeatureFlagName('dark-mode')));
    }

    #[Test]
    public function feature_flag_disabled_by_default(): void
    {
        $store = new InMemoryFeatureFlagStore();
        $evaluator = new EvaluateFeatureFlag($store);

        self::assertFalse($evaluator->isEnabled(new FeatureFlagName('nonexistent-flag')));
    }

    #[Test]
    public function feature_flag_unknown_flag_returns_false_not_error(): void
    {
        $store = new InMemoryFeatureFlagStore();
        $evaluator = new EvaluateFeatureFlag($store);

        // Unknown flag safely returns false (safe default)
        self::assertFalse($evaluator->isEnabled(new FeatureFlagName('completely-unknown')));
    }

    #[Test]
    public function feature_flag_environment_override(): void
    {
        $store = new InMemoryFeatureFlagStore();
        $store->set(new FeatureFlag(
            new FeatureFlagName('beta-feature'),
            FeatureFlagState::Disabled,
            ['production' => 'disabled', 'staging' => 'enabled'],
        ));

        $prodEvaluator = new EvaluateFeatureFlag($store, 'production');
        $stagingEvaluator = new EvaluateFeatureFlag($store, 'staging');

        self::assertFalse($prodEvaluator->isEnabled(new FeatureFlagName('beta-feature')));
        self::assertTrue($stagingEvaluator->isEnabled(new FeatureFlagName('beta-feature')));
    }

    // ===== Service Discovery Tests =====

    #[Test]
    public function service_endpoint_registration_and_resolution(): void
    {
        $registry = new InMemoryServiceRegistry();
        $name = new ServiceName('user-service');
        $endpoint = new ServiceEndpoint('http://users.internal:8080');

        $registry->register($name, $endpoint);

        $resolved = $registry->resolve($name);

        self::assertCount(1, $resolved);
        self::assertSame('http://users.internal:8080', $resolved[0]->url);
    }

    #[Test]
    public function service_deregistration(): void
    {
        $registry = new InMemoryServiceRegistry();
        $name = new ServiceName('cache-service');
        $endpoint1 = new ServiceEndpoint('http://cache1:6379');
        $endpoint2 = new ServiceEndpoint('http://cache2:6379');

        $registry->register($name, $endpoint1);
        $registry->register($name, $endpoint2);

        self::assertCount(2, $registry->resolve($name));

        $registry->deregister($name, $endpoint1);

        $resolved = $registry->resolve($name);
        self::assertCount(1, $resolved);
        self::assertSame('http://cache2:6379', $resolved[0]->url);
    }

    #[Test]
    public function service_endpoint_validation(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new ServiceEndpoint('not-a-valid-url');
    }

    // ===== Security Doctor Tests =====

    #[Test]
    public function security_doctor_reports_healthy_with_config(): void
    {
        $check = new CheckSecurityRuntime(
            signingSecret: 'some-secret',
            policyEngineAvailable: true,
            featureFlagsAvailable: true,
            serviceDiscoveryAvailable: true,
            redactionAvailable: true,
        );

        $findings = $check->check();
        $greenCount = count(array_filter($findings, static fn ($f) => $f->severity === DoctorSeverity::Green));

        self::assertCount(5, $findings);
        self::assertSame(5, $greenCount);
    }

    #[Test]
    public function security_doctor_finds_missing_signing_config(): void
    {
        $check = new CheckSecurityRuntime(
            signingSecret: '',
            policyEngineAvailable: true,
            featureFlagsAvailable: true,
            serviceDiscoveryAvailable: true,
            redactionAvailable: true,
        );

        $findings = $check->check();
        $signingFinding = $findings[0];

        self::assertSame('Request Signing', $signingFinding->check);
        self::assertSame(DoctorSeverity::Yellow, $signingFinding->severity);
    }

    #[Test]
    public function security_doctor_reports_unavailable_policy_engine(): void
    {
        $check = new CheckSecurityRuntime(
            signingSecret: 'secret',
            policyEngineAvailable: false,
            featureFlagsAvailable: true,
            serviceDiscoveryAvailable: true,
            redactionAvailable: true,
        );

        $findings = $check->check();
        $policyFinding = $findings[1];

        self::assertSame('Policy Engine', $policyFinding->check);
        self::assertSame(DoctorSeverity::Red, $policyFinding->severity);
    }

    #[Test]
    public function signature_timestamp_tolerance_check(): void
    {
        $timestamp = new SignatureTimestamp(time() - 100);

        self::assertTrue($timestamp->isWithinTolerance(300));
        self::assertFalse($timestamp->isWithinTolerance(60));
    }

    #[Test]
    public function reject_expired_signature(): void
    {
        $oldTimestamp = new SignatureTimestamp(time() - 600);

        $result = RejectExpiredSignature::check($oldTimestamp, 300, time());

        self::assertFalse($result->valid);
        self::assertStringContainsString('expired', $result->reason);
    }

    #[Test]
    public function reject_replayed_nonce(): void
    {
        $nonceStore = new NonceStore();
        $nonceStore->store('used-nonce', time());

        $result = RejectReplayedNonce::check('used-nonce', $nonceStore, time());

        self::assertFalse($result->valid);
        self::assertStringContainsString('replay', $result->reason);
    }

    #[Test]
    public function policy_decision_is_allowed_helper(): void
    {
        $decision = PolicyDecision::allow('Test reason');

        self::assertTrue($decision->isAllowed());
        self::assertSame('Test reason', $decision->reason);
    }

    #[Test]
    public function policy_decision_deny_helper(): void
    {
        $decision = PolicyDecision::deny('Test deny');

        self::assertFalse($decision->isAllowed());
        self::assertSame(PolicyEffect::Deny, $decision->effect);
    }

    #[Test]
    public function feature_flag_list_all(): void
    {
        $store = new InMemoryFeatureFlagStore();
        $store->set(new FeatureFlag(new FeatureFlagName('flag-a'), FeatureFlagState::Enabled));
        $store->set(new FeatureFlag(new FeatureFlagName('flag-b'), FeatureFlagState::Disabled));

        $evaluator = new EvaluateFeatureFlag($store);

        $all = $evaluator->all();

        self::assertCount(2, $all);
    }

    #[Test]
    public function service_registry_list_services(): void
    {
        $registry = new InMemoryServiceRegistry();
        $registry->register(new ServiceName('svc-a'), new ServiceEndpoint('http://a:80'));
        $registry->register(new ServiceName('svc-b'), new ServiceEndpoint('http://b:80'));

        $services = $registry->listServices();

        self::assertCount(2, $services);
    }

    #[Test]
    public function nonce_store_clear(): void
    {
        $store = new NonceStore();
        $store->store('n1', time());
        $store->store('n2', time());

        self::assertTrue($store->has('n1'));

        $store->clear();

        self::assertFalse($store->has('n1'));
        self::assertFalse($store->has('n2'));
    }

    #[Test]
    public function signature_nonce_must_not_be_empty(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new SignatureNonce('');
    }

    #[Test]
    public function signature_key_id_must_not_be_empty(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new SignatureKeyId('');
    }

    #[Test]
    public function feature_flag_name_must_not_be_empty(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new FeatureFlagName('');
    }

    #[Test]
    public function service_name_must_not_be_empty(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new ServiceName('');
    }

    #[Test]
    public function policy_with_multiple_rules_deny_takes_precedence(): void
    {
        $policy = (new DefinePolicy('complex'))
            ->allow('allow-read', static fn (
                PolicySubject $subject,
                PolicyAction $action,
                PolicyResource $resource,
                PolicyContext $context,
            ) => $action->name === 'read')
            ->deny('deny-sensitive', static fn (
                PolicySubject $subject,
                PolicyAction $action,
                PolicyResource $resource,
                PolicyContext $context,
            ) => $resource->type === 'secret');

        $evaluator = new EvaluatePolicy();

        // Allow rule matches (read action)
        $allowResult = $evaluator->evaluate(
            $policy,
            new PolicySubject('user-1'),
            new PolicyAction('read'),
            new PolicyResource('document', 'doc-1'),
        );
        self::assertTrue($allowResult->isAllowed());

        // Deny rule also matches (secret resource) — deny wins
        $denyResult = $evaluator->evaluate(
            $policy,
            new PolicySubject('user-1'),
            new PolicyAction('read'),
            new PolicyResource('secret', 'secret-1'),
        );
        self::assertFalse($denyResult->isAllowed());
    }

    #[Test]
    public function signature_payload_from_headers_throws_on_missing(): void
    {
        $this->expectException(InvalidArgumentException::class);
        SignaturePayload::fromHeaders([]);
    }
}
