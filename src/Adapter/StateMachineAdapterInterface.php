<?php

declare(strict_types=1);

namespace ParadiseSecurity\Bundle\StateMachineBundle\Adapter;

interface StateMachineAdapterInterface
{
    public function can(object $subject, string $graphName, string $transition): bool;

    public function apply(object $subject, string $graphName, string $transition, array $context = []): void;

    public function getEnabledTransitions(object $subject, string $graphName): array;

    public function getTransitionFromState(object $subject, string $graphName, string $fromState): ?string;

    public function getTransitionToState(object $subject, string $graphName, string $toState): ?string;
}
