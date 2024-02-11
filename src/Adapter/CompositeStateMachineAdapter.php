<?php

declare(strict_types=1);

namespace ParadiseSecurity\Bundle\StateMachineBundle\Adapter;

use Traversable;
use Webmozart\Assert\Assert;

use function iterator_to_array;
use function sprintf;

class CompositeStateMachineAdapter implements StateMachineAdapterInterface
{
    private array $stateMachineAdapters;

    public function __construct(
        iterable $stateMachineAdapters,
        private string $defaultAdapter,
        private array $graphsToAdaptersMapping,
    ) {
        Assert::notEmpty($stateMachineAdapters, 'At least one state machine adapter should be provided.');
        Assert::allIsInstanceOf(
            $stateMachineAdapters,
            StateMachineAdapterInterface::class,
            sprintf('All state machine adapters should implement the "%s" interface.', StateMachineAdapterInterface::class),
        );
        $this->stateMachineAdapters = $stateMachineAdapters instanceof Traversable ? iterator_to_array($stateMachineAdapters) : $stateMachineAdapters;
    }

    public function can(object $subject, string $graphName, string $transition): bool
    {
        return $this->getStateMachineAdapter($graphName)->can($subject, $graphName, $transition);
    }

    public function apply(
        object $subject,
        string $graphName,
        string $transition,
        array $context = []
    ): void {
        $this->getStateMachineAdapter($graphName)->apply($subject, $graphName, $transition, $context);
    }

    public function getEnabledTransitions(object $subject, string $graphName): array
    {
        return $this->getStateMachineAdapter($graphName)->getEnabledTransitions($subject, $graphName);
    }

    public function getTransitionFromState(
        object $subject,
        string $graphName,
        string $fromState
    ): ?string {
        return $this->getStateMachineAdapter($graphName)->getTransitionFromState($subject, $graphName, $fromState);
    }

    public function getTransitionToState(
        object $subject,
        string $graphName,
        string $toState
    ): ?string {
        return $this->getStateMachineAdapter($graphName)->getTransitionToState($subject, $graphName, $toState);
    }

    private function getStateMachineAdapter(string $graphName): StateMachineAdapterInterface
    {
        if (isset($this->graphsToAdaptersMapping[$graphName])) {
            return $this->stateMachineAdapters[$this->graphsToAdaptersMapping[$graphName]];
        }

        return $this->stateMachineAdapters[$this->defaultAdapter];
    }
}
