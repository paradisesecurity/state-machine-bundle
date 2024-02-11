<?php

declare(strict_types=1);

namespace ParadiseSecurity\Bundle\StateMachineBundle\Adapter;

use ParadiseSecurity\Bundle\StateMachineBundle\Exception\StateMachineExecutionException;
use ParadiseSecurity\Bundle\StateMachineBundle\Transition\Transition;
use ParadiseSecurity\Bundle\StateMachineBundle\Transition\TransitionInterface;
use SM\Factory\FactoryInterface;
use SM\SMException;
use SM\StateMachine\StateMachineInterface;

use function array_filter;
use function in_array;

final class WinzouStateMachineAdapter implements StateMachineAdapterInterface
{
    public function __construct(private FactoryInterface $winzouStateMachineFactory)
    {
    }

    public function can(object $subject, string $graphName, string $transition): bool
    {
        try {
            return $this->getStateMachine($subject, $graphName)->can($transition);
        } catch (SMException $exception) {
            throw new StateMachineExecutionException($exception->getMessage(), $exception->getCode(), $exception);
        }
    }

    public function apply(
        object $subject,
        string $graphName,
        string $transition,
        array $context = []
    ): void {
        try {
            $this->getStateMachine($subject, $graphName)->apply($transition);
        } catch (SMException $exception) {
            throw new StateMachineExecutionException($exception->getMessage(), $exception->getCode(), $exception);
        }
    }

    public function getEnabledTransitions(object $subject, string $graphName): array
    {
        $stateMachine = $this->getStateMachine($subject, $graphName);

        return array_filter(
            $this->getAllTransitions($stateMachine),
            fn (TransitionInterface $transition) => $this->can($subject, $graphName, $transition->getName()),
        );
    }

    private function getAllTransitions(StateMachineInterface $stateMachine): array
    {
        try {
            $transitionsConfig = $this->getConfig($stateMachine)['transitions'];
        } catch (\ReflectionException $exception) {
            throw new StateMachineExecutionException($exception->getMessage(), $exception->getCode(), $exception);
        }

        $transitions = [];

        foreach ($transitionsConfig as $transitionName => $transitionConfig) {
            $froms = $transitionConfig['from'];
            $tos = [$transitionConfig['to']];
            $transitions[] = new Transition($transitionName, $froms, $tos);
        }

        return $transitions;
    }

    private function getConfig(StateMachineInterface $stateMachine): array
    {
        $reflection = new \ReflectionClass($stateMachine);
        $configProperty = $reflection->getProperty('config');
        $configProperty->setAccessible(true);

        return  $configProperty->getValue($stateMachine);
    }

    public function getTransitionFromState(
        object $subject,
        string $graphName,
        string $fromState
    ): ?string {
        foreach ($this->getEnabledTransitions($subject, $graphName) as $transition) {
            if ($transition->getFroms() !== null && in_array($fromState, $transition->getFroms(), true)) {
                return $transition->getName();
            }
        }

        return null;
    }

    public function getTransitionToState(
        object $subject,
        string $graphName,
        string $toState
    ): ?string {
        foreach ($this->getEnabledTransitions($subject, $graphName) as $transition) {
            if ($transition->getTos() !== null && in_array($toState, $transition->getTos(), true)) {
                return $transition->getName();
            }
        }

        return null;
    }

    private function getStateMachine(object $subject, string $graphName): StateMachineInterface
    {
        try {
            return $this->winzouStateMachineFactory->get($subject, $graphName);
        } catch (SMException $exception) {
            throw new StateMachineExecutionException($exception->getMessage(), $exception->getCode(), $exception);
        }
    }
}
