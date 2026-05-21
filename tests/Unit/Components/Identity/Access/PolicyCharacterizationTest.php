<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\Identity\Access;

use Avax\Components\Identity\Access\System\Capabilities\Policy\Engine\PolicyEvaluator;
use Avax\Components\Identity\Access\System\Capabilities\Policy\Foundation\DecisionExplanation;
use Avax\Components\Identity\Access\System\Capabilities\Policy\Foundation\PolicyDecision;
use Avax\Components\Identity\Access\System\Capabilities\Policy\Policy;
use Avax\Components\Identity\Access\System\Capabilities\Policy\Rules\AttributeCondition;
use Avax\Components\Identity\Access\System\Capabilities\Policy\Rules\PolicyRule;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

final class PolicyCharacterizationTest extends TestCase
{
    #[Test]
    public function policyDecisionCanBeCreated(): void
    {
        $decision = new PolicyDecision(true, 'test reason');
        self::assertTrue($decision->allowed);
        self::assertSame('test reason', $decision->reason);
    }

    #[Test]
    public function policyDecisionAllowFactory(): void
    {
        $decision = PolicyDecision::allow('allowed');
        self::assertTrue($decision->allowed);
        self::assertSame('allowed', $decision->reason);
    }

    #[Test]
    public function policyDecisionAllowFactoryNullReason(): void
    {
        $decision = PolicyDecision::allow();
        self::assertTrue($decision->allowed);
        self::assertNull($decision->reason);
    }

    #[Test]
    public function policyDecisionDenyFactory(): void
    {
        $decision = PolicyDecision::deny('denied');
        self::assertFalse($decision->allowed);
        self::assertSame('denied', $decision->reason);
    }

    #[Test]
    public function policyDecisionIsFinalReadonly(): void
    {
        $reflection = new ReflectionClass(PolicyDecision::class);
        self::assertTrue($reflection->isFinal());
    }

    #[Test]
    public function decisionExplanationCanBeCreated(): void
    {
        $explanation = new DecisionExplanation(true, ['rule 1', 'rule 2']);
        self::assertTrue($explanation->allowed);
        self::assertSame(['rule 1', 'rule 2'], $explanation->reasons);
    }

    #[Test]
    public function decisionExplanationToStringWhenAllowed(): void
    {
        $explanation = new DecisionExplanation(true, ['role matches', 'owner matches']);
        self::assertSame('ALLOWED: role matches AND owner matches', $explanation->toString());
    }

    #[Test]
    public function decisionExplanationToStringWhenDenied(): void
    {
        $explanation = new DecisionExplanation(false, ['role mismatch']);
        self::assertSame('DENIED: role mismatch', $explanation->toString());
    }

    #[Test]
    public function decisionExplanationIsFinalReadonly(): void
    {
        $reflection = new ReflectionClass(DecisionExplanation::class);
        self::assertTrue($reflection->isFinal());
    }

    #[Test]
    public function policyEvaluatorEvaluateReturnsAllowWithNoRules(): void
    {
        $evaluator = new PolicyEvaluator();
        $resource  = new \stdClass();

        $decision = $evaluator->evaluate('read', $resource, []);

        self::assertTrue($decision->allowed);
    }

    #[Test]
    public function policyEvaluatorEvaluateWithMatchingRule(): void
    {
        $evaluator = new PolicyEvaluator();
        $resource  = new \stdClass();
        $resource->owner_id = 42;

        $evaluator->register(new PolicyRule(
            action: 'read',
            condition: fn(object $r, array $c): bool => ($c['user_id'] ?? null) === ($r->owner_id ?? null),
            reason: 'Owner matches',
        ));

        $decision = $evaluator->evaluate('read', $resource, ['user_id' => 42]);

        self::assertTrue($decision->allowed);
        self::assertSame('Matched 1 rules', $decision->reason);
    }

    #[Test]
    public function policyEvaluatorEvaluateWithNonMatchingRule(): void
    {
        $evaluator = new PolicyEvaluator();
        $resource  = new \stdClass();
        $resource->owner_id = 42;

        $evaluator->register(new PolicyRule(
            action: 'read',
            condition: fn(object $r, array $c): bool => ($c['user_id'] ?? null) === ($r->owner_id ?? null),
            reason: 'Owner matches',
        ));

        $decision = $evaluator->evaluate('read', $resource, ['user_id' => 99]);

        self::assertFalse($decision->allowed);
        self::assertSame('Owner matches', $decision->reason);
    }

    #[Test]
    public function policyEvaluatorEvaluateSkipsNonApplicableRules(): void
    {
        $evaluator = new PolicyEvaluator();
        $resource  = new \stdClass();

        $evaluator->register(new PolicyRule(
            action: 'delete',
            condition: fn(): bool => true,
            reason: 'Should not apply',
        ));

        $decision = $evaluator->evaluate('read', $resource, []);

        self::assertTrue($decision->allowed);
        self::assertNull($decision->reason);
    }

    #[Test]
    public function policyEvaluatorExplainReturnsReasons(): void
    {
        $evaluator = new PolicyEvaluator();
        $resource  = new \stdClass();

        $evaluator->register(new PolicyRule(
            'read',
            fn(): bool => true,
            'First rule',
        ));

        $evaluator->register(new PolicyRule(
            'read',
            fn(): bool => true,
            'Second rule',
        ));

        $explanation = $evaluator->explain('read', $resource, []);

        self::assertTrue($explanation->allowed);
        self::assertCount(2, $explanation->reasons);
    }

    #[Test]
    public function policyRuleAppliesChecksAction(): void
    {
        $rule = new PolicyRule(
            action: 'read',
            condition: fn(): bool => true,
            reason: 'test',
        );

        self::assertTrue($rule->applies('read'));
        self::assertFalse($rule->applies('write'));
    }

    #[Test]
    public function policyRuleEvaluateReturnsNullWhenConditionReturnsNull(): void
    {
        $rule = new PolicyRule(
            action: 'read',
            condition: fn(): null => null,
            reason: 'test',
        );

        $result = $rule->evaluate('read', new \stdClass(), []);

        self::assertNull($result);
    }

    #[Test]
    public function policyRuleEvaluateReturnsDecisionWhenConditionReturnsTrue(): void
    {
        $rule = new PolicyRule(
            action: 'read',
            condition: fn(): bool => true,
            reason: 'Always allowed',
        );

        $result = $rule->evaluate('read', new \stdClass(), []);

        self::assertNotNull($result);
        self::assertTrue($result->allowed);
        self::assertSame('Always allowed', $result->reason);
    }

    #[Test]
    public function policyRuleEvaluateReturnsDecisionWhenConditionReturnsFalse(): void
    {
        $rule = new PolicyRule(
            action: 'read',
            condition: fn(): bool => false,
            reason: 'Always denied',
        );

        $result = $rule->evaluate('read', new \stdClass(), []);

        self::assertNotNull($result);
        self::assertFalse($result->allowed);
        self::assertSame('Always denied', $result->reason);
    }

    #[Test]
    public function attributeConditionOwner(): void
    {
        $resource       = new \stdClass();
        $resource->owner_id = 42;

        self::assertTrue(AttributeCondition::owner($resource, ['user_id' => 42]));
        self::assertFalse(AttributeCondition::owner($resource, ['user_id' => 99]));
    }

    #[Test]
    public function attributeConditionRole(): void
    {
        $resource = new \stdClass();

        self::assertTrue(AttributeCondition::role('admin', $resource, ['role' => 'admin']));
        self::assertFalse(AttributeCondition::role('admin', $resource, ['role' => 'user']));
    }

    #[Test]
    public function attributeConditionIpWhitelist(): void
    {
        $resource = new \stdClass();

        self::assertTrue(AttributeCondition::ipWhitelist(['10.0.0.1', '10.0.0.2'], $resource, ['ip' => '10.0.0.1']));
        self::assertFalse(AttributeCondition::ipWhitelist(['10.0.0.1'], $resource, ['ip' => '10.0.0.99']));
    }

    #[Test]
    public function attributeConditionWithinHoursUsesProvidedTimeContext(): void
    {
        $currentTime = new DateTimeImmutable('2026-05-21 14:30:00');

        self::assertTrue(AttributeCondition::withinHours(9, 17, $currentTime));
        self::assertFalse(AttributeCondition::withinHours(15, 17, $currentTime));
    }

    #[Test]
    public function staticPolicyDefineAndAuthorize(): void
    {
        Policy::define('admin-only', ['role' => 'admin']);

        self::assertTrue(Policy::authorize('admin-only', ['role' => 'admin']));
        self::assertFalse(Policy::authorize('admin-only', ['role' => 'user']));
    }

    #[Test]
    public function staticPolicyAllowsAndRegister(): void
    {
        $resource = new \stdClass();
        $resource->owner_id = 1;

        Policy::register(new PolicyRule(
            action: 'edit',
            condition: fn(object $r, array $c): bool => ($c['user_id'] ?? null) === ($r->owner_id ?? null),
            reason: 'Owner check',
        ));

        $decision = Policy::allows('edit', $resource, ['user_id' => 1]);
        self::assertTrue($decision->allowed);

        $decision = Policy::allows('edit', $resource, ['user_id' => 2]);
        self::assertFalse($decision->allowed);
    }

    #[Test]
    public function staticPolicyExplain(): void
    {
        $resource = new \stdClass();

        Policy::register(new PolicyRule(
            action: 'view',
            condition: fn(): bool => true,
            reason: 'Always viewable',
        ));

        $explanation = Policy::explain('view', $resource, []);
        self::assertTrue($explanation->allowed);
    }

    #[Test]
    public function duplicateFilesAreRemoved(): void
    {
        $deletedPaths = [
            __DIR__ . '/../../../../../../components/Identity/Access/System/Capabilities/Policy/PublicSurface/Policy.php',
            __DIR__ . '/../../../../../../components/Identity/Access/System/Capabilities/Policy/Capabilities',
            __DIR__ . '/../../../../../../components/Identity/Access/System/Capabilities/Policy/Rules/PolicyDecision.php',
            __DIR__ . '/../../../../../../components/Identity/Access/System/Capabilities/Policy/Engine/DecisionExplanation.php',
            __DIR__ . '/../../../../../../components/Identity/Access/System/Capabilities/Policy/Engine/AttributeCondition.php',
        ];

        foreach ($deletedPaths as $path) {
            $resolved = realpath($path);
            self::assertFalse(
                $resolved !== false && file_exists($resolved),
                "Duplicate file should have been removed: {$path}",
            );
        }
    }

    #[Test]
    public function canonicalClassesExist(): void
    {
        self::assertTrue(class_exists(PolicyDecision::class));
        self::assertTrue(class_exists(DecisionExplanation::class));
        self::assertTrue(class_exists(Policy::class));
        self::assertTrue(class_exists(PolicyEvaluator::class));
        self::assertTrue(class_exists(PolicyRule::class));
        self::assertTrue(class_exists(AttributeCondition::class));
    }
}
