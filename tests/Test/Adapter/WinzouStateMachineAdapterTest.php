<?php

declare(strict_types=1);

namespace ParadiseSecurity\Bundle\StateMachineBundle\Test\Adapter;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ParadiseSecurity\Bundle\StateMachineBundle\Adapter\StateMachineAdapterInterface;
use ParadiseSecurity\Bundle\StateMachineBundle\Adapter\WinzouStateMachineAdapter;
use ParadiseSecurity\Bundle\StateMachineBundle\Exception\StateMachineExecutionException;
use SM\Factory\FactoryInterface;
use SM\SMException;
use SM\StateMachine\StateMachine as WinzouStateMachine;

final class WinzouStateMachineAdapterTest extends TestCase
{
    private FactoryInterface&MockObject $winzouStateMachineFactory;

    private WinzouStateMachine&MockObject $winzouStateMachine;

    protected function setUp(): void
    {
        parent::setUp();

        $this->winzouStateMachine = $this->createMock(WinzouStateMachine::class);
        $this->setStateMachineConfig($this->winzouStateMachine, [
            'transitions' => [
                'transition' => [
                    'from' => ['from_state'],
                    'to' => 'to_state',
                ],
                'another_transition' => [
                    'from' => ['another_from_state'],
                    'to' => 'another_to_state',
                ],
            ],
        ]);

        $this->winzouStateMachineFactory = $this->createMock(FactoryInterface::class);
        $this->winzouStateMachineFactory
            ->method('get')
            ->willReturn($this->winzouStateMachine)
        ;
    }

    public function testReturnWhetherTransitionCanBeApplied(): void
    {
        $this->winzouStateMachine->method('can')->willReturn(true);

        $subject = new \stdClass();
        $graphName = 'graph_name';
        $transition = 'transition';

        $this->assertTrue($this->createTestSubject()->can($subject, $graphName, $transition));
    }

    public function testAppliesTransition(): void
    {
        $this->winzouStateMachine->expects($this->once())->method('apply');

        $subject = new \stdClass();
        $graphName = 'graph_name';
        $transition = 'transition';

        $this->createTestSubject()->apply($subject, $graphName, $transition);
    }

    public function testReturnsEnabledTransitions(): void
    {
        $subject = new \stdClass();
        $graphName = 'graph_name';

        $this->winzouStateMachine->method('can')->willReturnMap([
            ['transition', true],
            ['another_transition', false],
        ]);

        $transitions = $this->createTestSubject()->getEnabledTransitions($subject, $graphName);

        $this->assertCount(1, $transitions);
        $this->assertSame('transition', $transitions[0]->getName());
        $this->assertSame(['from_state'], $transitions[0]->getFroms());
        $this->assertSame(['to_state'], $transitions[0]->getTos());
    }

    public function testConvertsWorkflowExceptionsToCustomOnesOnCan(): void
    {
        $this->expectException(StateMachineExecutionException::class);

        $subject = new \stdClass();
        $graphName = 'graph_name';
        $transition = 'transition';

        $this->winzouStateMachineFactory->method('get')->willThrowException(new SMException());

        $this->createTestSubject()->can($subject, $graphName, $transition);
    }

    public function testConvertsWorkflowExceptionsToCustomOnApply(): void
    {
        $this->expectException(StateMachineExecutionException::class);

        $subject = new \stdClass();
        $graphName = 'graph_name';
        $transition = 'transition';

        $this->winzouStateMachineFactory->method('get')->willThrowException(new SMException());

        $this->createTestSubject()->apply($subject, $graphName, $transition);
    }

    public function testConvertsWorkflowExceptionsToCustomOnGetEnabledTransitions(): void
    {
        $this->expectException(StateMachineExecutionException::class);

        $subject = new \stdClass();
        $graphName = 'graph_name';

        $this->winzouStateMachineFactory->method('get')->willThrowException(new SMException());

        $this->createTestSubject()->getEnabledTransitions($subject, $graphName);
    }

    public function testReturnsTransitionsToForGivenTransition(): void
    {
        $this->setStateMachineConfig($this->winzouStateMachine, [
            'transitions' => [
                'transition_to_state' => [
                    'from' => ['from_state'],
                    'to' => 'to_state',
                ],
            ],
        ]);

        $this->winzouStateMachine->method('can')->willReturn(true);

        $this->winzouStateMachineFactory = $this->createMock(FactoryInterface::class);
        $this->winzouStateMachineFactory->method('get')->willReturn($this->winzouStateMachine);

        $stateMachine = $this->createTestSubject();

        $this->assertSame(
            'transition_to_state',
            $stateMachine->getTransitionToState(new \stdClass(), 'graph_name', 'to_state'),
        );
    }

    public function testReturnsTransitionsFromForGivenTransition(): void
    {
        $this->setStateMachineConfig($this->winzouStateMachine, [
            'transitions' => [
                'transition_from_state' => [
                    'from' => ['from_state'],
                    'to' => 'to_state',
                ],
            ],
        ]);

        $this->winzouStateMachine->method('can')->willReturn(true);

        $this->winzouStateMachineFactory = $this->createMock(FactoryInterface::class);
        $this->winzouStateMachineFactory->method('get')->willReturn($this->winzouStateMachine);

        $stateMachine = $this->createTestSubject();

        $this->assertSame(
            'transition_from_state',
            $stateMachine->getTransitionFromState(new \stdClass(), 'graph_name', 'from_state'),
        );
    }

    private function createTestSubject(): StateMachineAdapterInterface
    {
        return new WinzouStateMachineAdapter($this->winzouStateMachineFactory);
    }

    private function setStateMachineConfig(WinzouStateMachine $stateMachine, array $config): void
    {
        $reflection = new \ReflectionClass($stateMachine);
        $configProperty = $reflection->getProperty('config');
        $configProperty->setAccessible(true);
        $configProperty->setValue($stateMachine, $config);
    }
}
