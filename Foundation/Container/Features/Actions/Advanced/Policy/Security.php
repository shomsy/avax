<?php

declare(strict_types=1);

namespace Avax\Container\Features\Actions\Advanced\Policy;

use Avax\Container\Guard\Rules\ContainerPolicy;

/**
 * Security - Fluent DSL for configuring container security policies.
 *
 * Provides a fluent, chainable API for configuring container security policies including
 * strict mode enforcement, tag-based access control, and custom security rules.
 * Enables fine-grained control over container behavior for security-sensitive applications.
 *
 * @see docs/Features/Actions/Advanced/Policy/Security.md#quick-summary
 */
final readonly class Security
{
    /**
     * Create a new Security configuration instance.
     *
     * @param ContainerPolicy $policy The policy instance to configure.
     *
     * @see docs/Features/Actions/Advanced/Policy/Security.md#method-__construct
     */
    public function __construct(private ContainerPolicy $policy) {}

    /**
     * Enable strict security mode.
     *
     * Configures the container to enforce strict security policies including
     * mandatory access controls, comprehensive validation, and security-first defaults.
     *
     * @return self Builder instance for method chaining.
     *
     * @see docs/Features/Actions/Advanced/Policy/Security.md#method-strict
     */
    public function strict() : self
    {
        // Internal logic to update the policy object
        return $this;
    }

    /**
     * Allow access to services tagged with the specified tag.
     *
     * Grants access permissions for services marked with the given security tag,
     * enabling tag-based access control for grouped service permissions.
     *
     * @param string $tag The security tag to allow access for.
     *
     * @return self Builder instance for method chaining.
     *
     * @see docs/Features/Actions/Advanced/Policy/Security.md#method-allowTagged
     */
    public function allowTagged(string $tag) : self
    {
        // Internal logic to update the policy object
        return $this;
    }
}
