<?php

declare(strict_types=1);

namespace ParadiseSecurity\Bundle\StateMachineBundle\StateMachine;

use SM\StateMachine\StateMachineInterface as BaseStateMachineInterface;

interface StateMachineInterface extends BaseStateMachineInterface
{
    /**
     * Returns the possible transition from given state
     * Returns null if no transition is possible
     */
    public function getTransitionFromState(string $fromState): ?string;

    /**
     * Returns the possible transition to the given state
     * Returns null if no transition is possible
     */
    public function getTransitionToState(string $toState): ?string;
}
