<?php

declare(strict_types=1);

namespace Avax\Tooling\Rector\NoForbiddenNamespaceDirRector;

use PhpParser\Node;
use PhpParser\Node\Stmt\Namespace_;
use Rector\Contract\Rector\RectorInterface;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * Custom Rector rule that forbids AvaX-banned namespace segments.
 *
 * AvaX governance (AGENTS.md) forbids technical dumping-ground names such as
 * Services, Utils, Helpers, Managers, Common, Shared, etc.
 *
 * This rule reports an error when any of those names appear as a namespace
 * segment, keeping the repository compliant with the "folder says flow or
 * capability" law.
 */
final class NoForbiddenNamespaceDirRector extends AbstractRector implements RectorInterface
{
    /**
     * Forbidden namespace segments per AvaX governance.
     *
     * @var list<string>
     */
    private const FORBIDDEN_SEGMENTS
        = [
            'Services',
            'Utils',
            'Helpers',
            'Managers',
            'Common',
            'Shared',
            'Core',
            'Support',
            'Adapters',
            'Contracts',
            'Handlers',
            'Processors',
            'Domain',
            'Entities',
            'ValueObjects',
            'Aggregates',
            'Repositories',
            'Events',
            'Commands',
            'Queries',
            'CQRS',
            'EventSourcing',
            'Sagas',
            'Policies',
            'Specifications',
            'Diagnostics',
            'Tests',
            'Docs',
            'InternalSystem',
            'ExportedCapabilities',
        ];

    public function getNodeTypes() : array
    {
        return [Namespace_::class];
    }

    /**
     * @param Namespace_ $node
     */
    public function refactor(Node $node) : ?Node
    {
        $parts = $node->name?->parts ?? [];

        foreach ($parts as $part) {
            if (in_array($part, self::FORBIDDEN_SEGMENTS, true)) {
                // Do not auto-fix — the correct name depends on context.
                // Just report the violation via addErrorMessage.
                $this->addErrorMessage(
                    sprintf(
                        'AvaX governance: namespace segment "%s" is forbidden. '
                        . 'Use Flow/Capability naming instead. '
                        . 'See AGENTS.md §6 and §8.',
                        $part,
                    ),
                );

                return null;
            }
        }

        return null;
    }

    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition(
            'Forbids AvaX-banned namespace segments (Services, Utils, Helpers, etc.)',
            [
                new CodeSample(
                    <<<'CODE'
                        namespace Avax\Components\Data\Services;
                        CODE
                    ,
                    <<<'CODE'
                        // Error: AvaX governance forbids "Services" in namespace.
                        // Use capability naming instead, e.g.:
                        // namespace Avax\Components\Data\System\Capabilities\StoreObjects;
                        CODE
                    ,
                ),
            ],
        );
    }
}
