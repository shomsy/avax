<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\DataTransfer\System\Configuration;

use Closure;

final readonly class DataTransferConfig
{
    /**
     * @param array<string, class-string|object|callable|string> $valueCasters
     * @param array<string, object|callable>                     $validationRules
     */
    public function __construct(
        public UnknownFieldPolicy $unknownFieldPolicy = UnknownFieldPolicy::Reject,
        public bool               $allowPublicPropertyHydration = true,
        public bool               $collectUnknownFields = false,
        public int                $maxDepth = 32,
        private ?Closure          $namingPolicy = null,
        private array             $valueCasters = [],
        private array             $validationRules = [],
    ) {}

    public static function default() : self
    {
        return new self();
    }

    public static function legacy() : self
    {
        return new self(unknownFieldPolicy: UnknownFieldPolicy::Ignore);
    }

    public function inputNameFor(string $fieldName) : string
    {
        if (! $this->namingPolicy instanceof Closure) {
            return $fieldName;
        }

        return ($this->namingPolicy)($fieldName);
    }

    public function withUnknownFieldPolicy(UnknownFieldPolicy $unknownFieldPolicy) : self
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

    public function withNamingPolicy(Closure $policy) : self
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

    public function withValueCaster(string $class, object|callable|string $caster) : self
    {
        $casters         = $this->valueCasters;
        $casters[$class] = $caster;

        return new self(
            unknownFieldPolicy          : $this->unknownFieldPolicy,
            allowPublicPropertyHydration: $this->allowPublicPropertyHydration,
            collectUnknownFields        : $this->collectUnknownFields,
            maxDepth                    : $this->maxDepth,
            namingPolicy                : $this->namingPolicy,
            valueCasters                : $casters,
            validationRules             : $this->validationRules,
        );
    }

    public function withValidationRule(string $attributeClass, object|callable $rule) : self
    {
        $rules                  = $this->validationRules;
        $rules[$attributeClass] = $rule;

        return new self(
            unknownFieldPolicy          : $this->unknownFieldPolicy,
            allowPublicPropertyHydration: $this->allowPublicPropertyHydration,
            collectUnknownFields        : $this->collectUnknownFields,
            maxDepth                    : $this->maxDepth,
            namingPolicy                : $this->namingPolicy,
            valueCasters                : $this->valueCasters,
            validationRules             : $rules,
        );
    }

    public function casterFor(string $class) : object|callable|string|null
    {
        return $this->valueCasters[$class] ?? null;
    }

    public function validationRuleFor(string $attributeClass) : object|callable|null
    {
        return $this->validationRules[$attributeClass] ?? null;
    }
}
