<?php

declare(strict_types=1);

namespace ParadiseSecurity\Bundle\StateMachineBundle\Transition;

interface TransitionInterface
{
    public function getName(): string;

    public function getFroms(): ?array;

    public function getTos(): ?array;
}
