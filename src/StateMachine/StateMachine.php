<?php

declare(strict_types=1);

namespace ParadiseSecurity\Bundle\StateMachineBundle\StateMachine;

use SM\StateMachine\StateMachine as BaseStateMachine;

final class StateMachine extends BaseStateMachine implements StateMachineInterface
{
    public function getTransitionFromState(string $fromState): ?string
    {
        foreach ($this->getPossibleTransitions() as $transition) {
            $config = $this->config['transitions'][$transition];
            if (in_array($fromState, $config['from'], true)) {
                return $transition;
            }
        }

        return null;
    }

    public function getTransitionToState(string $toState): ?string
    {
        foreach ($this->getPossibleTransitions() as $transition) {
            $config = $this->config['transitions'][$transition];
            if ($toState === $config['to']) {
                return $transition;
            }
        }

        return null;
    }
}
