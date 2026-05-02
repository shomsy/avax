<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Framework\System\Capabilities\PreCommit;

use Avax\Framework\System\Capabilities\PreCommit\ValidationChain\ValidationChain;
use Avax\Framework\System\Capabilities\PreCommit\ValidationResult;
use Avax\Framework\System\Capabilities\PreCommit\Validators\ValidatorInterface;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for ValidationChain and ValidatorInterface.
 */
final class ValidationChainTest extends TestCase
{
    public function test_it_starts_empty(): void
    {
        $chain = new ValidationChain();

        self::assertSame([], $chain->getValidators());
    }

    public function test_it_adds_validators(): void
    {
        $chain = new ValidationChain();
        $v1 = new TestValidator('v1');
        $v2 = new TestValidator('v2');

        $chain->add($v1);
        $chain->add($v2);

        self::assertCount(2, $chain->getValidators());
    }

    public function test_it_returns_validator_by_name(): void
    {
        $chain = new ValidationChain();
        $v1 = new TestValidator('check-one');
        $v2 = new TestValidator('check-two');

        $chain->add($v1);
        $chain->add($v2);

        self::assertSame($v1, $chain->getValidator('check-one'));
        self::assertSame($v2, $chain->getValidator('check-two'));
        self::assertNull($chain->getValidator('nonexistent'));
    }

    public function test_it_resets_the_chain(): void
    {
        $chain = new ValidationChain();
        $chain->add(new TestValidator('v1'));
        $chain->add(new TestValidator('v2'));

        self::assertCount(2, $chain->getValidators());

        $chain->reset();

        self::assertSame([], $chain->getValidators());
    }

    public function test_it_validates_with_no_validators(): void
    {
        $chain = new ValidationChain();

        $result = $chain->validate([]);

        self::assertTrue($result->isPassed());
        self::assertContains('No validators configured', $result->getMessages());
    }

    public function test_it_validates_with_chain_enabled(): void
    {
        $chain = new ValidationChain();
        $v1 = new TestValidator('v1', true);
        $v2 = new TestValidator('v2', false, 'Validation failed');

        $chain->add($v1);
        $chain->add($v2);

        $result = $chain->validate(['test' => true]);

        self::assertTrue($result->isFailed());
        self::assertContains('Validation failed', $result->getMessages());
    }

    public function test_it_returns_validator_count(): void
    {
        $chain = new ValidationChain();

        self::assertCount(0, $chain->getValidators());

        $chain->add(new TestValidator('v1'));
        self::assertCount(1, $chain->getValidators());

        $chain->add(new TestValidator('v2'));
        self::assertCount(2, $chain->getValidators());

        $chain->add(new TestValidator('v3'));
        self::assertCount(3, $chain->getValidators());
    }

    public function test_it_fluently_returns_self_on_add(): void
    {
        $chain = new ValidationChain();
        $result = $chain->add(new TestValidator('v1'));

        self::assertSame($chain, $result);
    }
}

/**
 * Test implementation of ValidatorInterface for testing purposes.
 */
final class TestValidator implements ValidatorInterface
{
    private ?ValidatorInterface $next = null;

    public function __construct(
        private string $name,
        private bool $passes = true,
        private string $failureMessage = 'Validator failed'
    ) {}

    public function setNext(ValidatorInterface $validator): ValidatorInterface
    {
        $this->next = $validator;
        return $validator;
    }

    public function validate(array $context): ValidationResult
    {
        if (! $this->passes) {
            return ValidationResult::fail($this->failureMessage);
        }

        if ($this->next instanceof ValidatorInterface) {
            return $this->next->validate($context);
        }

        return ValidationResult::pass('All validators passed');
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function supports(array $context): bool
    {
        return true;
    }
}
