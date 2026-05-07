<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Tasks\System\Flows\RetryTask;

use Avax\Components\Operations\Tasks\System\Capabilities\TaskRetry\TaskRetryPolicy;
use Avax\Components\Operations\Tasks\System\Capabilities\TaskRunner\TaskRunner;

final readonly class RetryTask
{
    /**
     * @return array{success: bool, attempts: int, result: array<string, mixed>|null, error: string|null}
     */
    public function retry(TaskRunner $runner, callable $task, TaskRetryPolicy $policy) : array
    {
        $lastError = null;
        $attempts  = 0;
        $delays    = $policy->delays();

        foreach ($delays as $delay) {
            $attempts++;
            $result = $runner->run($task);

            if ($result['status'] === 'completed') {
                return [
                    'success'  => true,
                    'attempts' => $attempts,
                    'result'   => $result,
                    'error' => null,
                ];
            }

            $lastError = $result['error'];

            if ($delay > 0) {
                usleep($delay * 1000);
            }
        }

        return [
            'success'  => false,
            'attempts' => $attempts,
            'result' => null,
            'error'    => $lastError,
        ];
    }
}
