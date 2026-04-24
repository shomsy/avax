<?php

declare(strict_types=1);

namespace Avax\ApplicationWorkflow\Saga\ResumeSaga;

final readonly class RebuildSagaState
{
    public function describeResponsibility() : string
    {
        return 'rebuilds saga state from stored state or event history.';
    }

    public function rebuild(array $storedState, array $events) : array
    {
        if (! empty($storedState)) {
            return $storedState;
        }

        return $this->rebuildFromEvents($events);
    }

    private function rebuildFromEvents(array $events) : array
    {
        $state = [
            'status'          => 'pending',
            'completed_steps' => [],
            'current_step'    => null,
        ];

        foreach ($events as $event) {
            $state = $this->applyEvent($state, $event);
        }

        return $state;
    }

    private function applyEvent(array $state, array $event) : array
    {
        $type = $event['type'] ?? '';

        if ($type === 'step_completed') {
            $state['completed_steps'][] = $event['step'];
            $state['current_step']      = $event['next_step'] ?? null;
        }

        if ($type === 'step_failed') {
            $state['status'] = 'failed';
        }

        return $state;
    }
}