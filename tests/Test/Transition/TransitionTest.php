<?php

declare(strict_types=1);

namespace ParadiseSecurity\Bundle\StateMachineBundle\Test\Transition;

use PHPUnit\Framework\TestCase;
use ParadiseSecurity\Bundle\StateMachineBundle\Transition\Transition;

final class TransitionTest extends TestCase
{
    public function testReturnsItsName(): void
    {
        $this->assertSame('name', $this->createTestSubject()->getName());
    }

    public function testReturnsItsFroms(): void
    {
        $this->assertSame(['from'], $this->createTestSubject()->getFroms());
    }

    public function testReturnsItsTos(): void
    {
        $this->assertSame(['to'], $this->createTestSubject()->getTos());
    }

    private function createTestSubject(): Transition
    {
        return new Transition('name', ['from'], ['to']);
    }
}
