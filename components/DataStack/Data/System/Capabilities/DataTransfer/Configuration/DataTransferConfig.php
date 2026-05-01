<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Data\System\Capabilities\DataTransfer\Configuration;

use Closure;

final readonly class DataTransferConfig
{
    /**
     * @param array<class-string, class-string|object|callable> $valueCasters
     * @param array<class-string, object|callable>              $validationRules
     */
    public function __construct(
        public UnknownFieldPolicy $unknownFieldPolicy = UnknownFieldPolicy::Reject,
        public bool $allowPublicPropertyHydration = true,
        public bool $collectUnknownFields = false,
        public int $maxDepth = 32,
        private ?Closure $namingPolicy = null,
        private array $valueCasters = [],
        private array $validationRules = [],
    ) {}

    public static function default(): self
    {
        return new self();
    }

    public static function legacy(): self
    {
        return new self(unknownFieldPolicy: UnknownFieldPolicy::Ignore);
    }

    public function inputNameFor(string $fieldName): string
    {
        if (! $this->namingPolicy instanceof Closure) {
            return $fieldName;
        }

        return ($this->namingPolicy)($fieldName);
    }

    public function withUnknownFieldPolicy(UnknownFieldPolicy $unknownFieldPolicy): self
    {
        return new self(
            unknownFieldPolicy          : $unknownFieldPolicy,
            allowPublicPropertyHydration: $this->allowPublicPropertyHydration,
            collectUnknownFields        : $this->collectUnknownFields,
            maxDepth                    : $this->maxDepth,
            namingPolicy                : $this->namingPolicy,
            valueCasters                : $this->valueCasters,
            validationRules             : $this->validationRules,
        );
    }

    public function withNamingPolicy(Closure $policy): self
    {
        return new self(
            unknownFieldPolicy          : $this->unknownFieldPolicy,
            allowPublicPropertyHydration: $this->allowPublicPropertyHydration,
            collectUnknownFields        : $this->collectUnknownFields,
            maxDepth                    : $this->maxDepth,
            namingPolicy                : $policy,
            valueCasters                : $this->valueCasters,
            validationRules             : $this->validationRules,
        );
    }

    public function withValueCaster(string $class, object|callable|string $caster): self
    {
        return new self(
            unknownFieldPolicy          : $this->unknownFieldPolicy,
            allowPublicPropertyHydration: $this->allowPublicPropertyHydration,
            collectUnknownFields        : $this->collectUnknownFields,
            maxDepth                    : $this->maxDepth,
            namingPolicy                : $this->namingPolicy,
            valueCasters                : [...$this->valueCasters, $class => $caster],
            validationRules             : $this->validationRules,
        );
    }

    public function withValidationRule(string $attributeClass, object|callable $rule): self
    {
        return new self(
            unknownFieldPolicy          : $this->unknownFieldPolicy,
            allowPublicPropertyHydration: $this->allowPublicPropertyHydration,
            collectUnknownFields        : $this->collectUnknownFields,
            maxDepth                    : $this->maxDepth,
            namingPolicy                : $this->namingPolicy,
            valueCasters                : $this->valueCasters,
            validationRules             : [...$this->validationRules, $attributeClass => $rule],
        );
    }

    public function casterFor(string $class): object|callable|string|null
    {
        return $this->valueCasters[$class] ?? null;
    }

    public function validationRuleFor(string $attributeClass): object|callable|null
    {
        return $this->validationRules[$attributeClass] ?? null;
    }
}
