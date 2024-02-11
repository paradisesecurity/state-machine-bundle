<?php

declare(strict_types=1);

namespace ParadiseSecurity\Bundle\StateMachineBundle\Test\Adapter;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ParadiseSecurity\Bundle\StateMachineBundle\Adapter\CompositeStateMachineAdapter;
use ParadiseSecurity\Bundle\StateMachineBundle\Adapter\StateMachineAdapterInterface;
use Webmozart\Assert\InvalidArgumentException;

final class CompositeStateMachineTest extends TestCase
{
    private StateMachineAdapterInterface&MockObject $someStateMachineAdapter;

    private StateMachineAdapterInterface&MockObject $anotherStateMachineAdapter;

    protected function setUp(): void
    {
        parent::setUp();

        $this->someStateMachineAdapter = $this->createMock(StateMachineAdapterInterface::class);
        $this->anotherStateMachineAdapter = $this->createMock(StateMachineAdapterInterface::class);
    }

    public function testThrowsAnExceptionIfNoStateMachineAdapterIsProvided(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('At least one state machine adapter should be provided.');

        $this->createTestSubject(stateMachineAdapters: []);
    }

    public function testThrowsAnExceptionIfStateMachineAdapterDoesNotImplementTheInterface(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('All state machine adapters should implement the "ParadiseSecurity\Bundle\StateMachineBundle\Adapter\StateMachineAdapterInterface" interface.');

        $this->createTestSubject(stateMachineAdapters: [$this->createMock(\stdClass::class)]);
    }

    public function testUsesMappedAdapterToUseWhetherTransitionCanBeApplied(): void
    {
        $this->someStateMachineAdapter->expects($this->never())->method('can');
        $this->anotherStateMachineAdapter->expects($this->once())->method('can')->willReturn(true);

        $stateMachine = $this->createTestSubject();
        $this->assertTrue($stateMachine->can(new \stdClass(), 'another_graph', 'some_transition'));
    }

    public function testAppliesTransitionUsingMappedAdapter(): void
    {
        $this->someStateMachineAdapter->expects($this->once())->method('apply');
        $this->anotherStateMachineAdapter->expects($this->never())->method('apply');

        $stateMachine = $this->createTestSubject();
        $stateMachine->apply(new \stdClass(), 'some_graph', 'some_transition');
    }

    public function testReturnsEnabledTransitionsUsingMappedAdapter(): void
    {
        $this->someStateMachineAdapter->expects($this->never())->method('getEnabledTransitions');
        $this->anotherStateMachineAdapter->expects($this->once())->method('getEnabledTransitions')->willReturn(['some_transition']);

        $stateMachine = $this->createTestSubject();
        $this->assertSame(['some_transition'], $stateMachine->getEnabledTransitions(new \stdClass(), 'another_graph'));
    }

    public function testReturnsTransitionFromStateUsingMappedAdapter(): void
    {
        $this->someStateMachineAdapter->expects($this->never())->method('getTransitionFromState');
        $this->anotherStateMachineAdapter->expects($this->once())->method('getTransitionFromState')->willReturn('some_transition');

        $stateMachine = $this->createTestSubject();
        $this->assertSame('some_transition', $stateMachine->getTransitionFromState(new \stdClass(), 'another_graph', 'some_state'));
    }

    public function testReturnsTransitionToStateUsingMappedAdapter(): void
    {
        $this->someStateMachineAdapter->expects($this->never())->method('getTransitionToState');
        $this->anotherStateMachineAdapter->expects($this->once())->method('getTransitionToState')->willReturn('some_transition');

        $stateMachine = $this->createTestSubject();
        $this->assertSame('some_transition', $stateMachine->getTransitionToState(new \stdClass(), 'another_graph', 'some_state'));
    }

    private function createTestSubject(mixed ...$arguments): StateMachineAdapterInterface
    {
        return new CompositeStateMachineAdapter(...array_replace([
            'stateMachineAdapters' => ['some_adapter' => $this->someStateMachineAdapter, 'another_adapter' => $this->anotherStateMachineAdapter],
            'defaultAdapter' => 'some_adapter',
            'graphsToAdaptersMapping' => ['some_graph' => 'some_adapter', 'another_graph' => 'another_adapter'],
        ], $arguments));
    }
}
