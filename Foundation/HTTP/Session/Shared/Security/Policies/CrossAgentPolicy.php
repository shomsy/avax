<?php

declare(strict_types=1);

namespace Avax\HTTP\Session\Shared\Security\Policies;

use Avax\HTTP\Session\Shared\Contracts\Security\ServerContext;
use Avax\HTTP\Session\Shared\Exceptions\PolicyViolationException;
use Avax\HTTP\Session\Shared\Security\NativeServerContext;

/**
 * CrossAgentPolicy - User Agent Consistency Policy
 *
 * Detects session hijacking by comparing User-Agent strings.
 * If User-Agent changes during session lifetime, policy is violated.
 *
 * Uses ServerContext for testability.
 */
final class CrossAgentPolicy implements PolicyInterface
{
    /**
     * CrossAgentPolicy Constructor.
     *
     * @param ServerContext|null $serverContext Server context (default: native).
     */
    public function __construct(
        private ServerContext|null $serverContext = null
    )
    {
        $this->serverContext ??= new NativeServerContext;
    }

    /**
     * {@inheritdoc}
     */
    public function enforce(array $data) : void
    {
        $storedAgent  = $data['_user_agent'] ?? null;
        $currentAgent = $this->serverContext->getUserAgent();

        // First time - store current agent
        if ($storedAgent === null) {
            return;
        }

        // Agent mismatch - possible hijacking
        if ($storedAgent !== $currentAgent) {
            throw PolicyViolationException::forPolicy(
                'cross_agent',
                'User Agent mismatch detected - possible session hijacking'
            );
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getName() : string
    {
        return 'cross_agent';
    }
}
