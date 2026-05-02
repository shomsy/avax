<?php

declare(strict_types=1);

namespace Avax\Framework\System\Capabilities\PreCommit\Capabilities;

use Avax\Framework\System\Capabilities\PreCommit\Models\PreCommitIssue;

interface CheckInterface
{
    /**
     * @param array<string, mixed> $context
     *
     * @return list<PreCommitIssue>
     */
    public function run(array $context) : array;
}
