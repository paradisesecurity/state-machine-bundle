<?php

declare(strict_types=1);

namespace ParadiseSecurity\Bundle\StateMachineBundle\Test\Adapter;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ParadiseSecurity\Bundle\StateMachineBundle\Adapter\SymfonyWorkflowAdapter;
use ParadiseSecurity\Bundle\StateMachineBundle\Exception\StateMachineExecutionException;
use Symfony\Component\Workflow\Exception\InvalidArgumentException;
use Symfony\Component\Workflow\Registry;
use Symfony\Component\Workflow\Transition as SymfonyWorkflowTransition;
use Symfony\Component\Workflow\Workflow;

final class SymfonyWorkflowAdapterTest extends TestCase
{
    private Registry&MockObject $symfonyWorkflowRegistry;

    protected function setUp(): void
    {
        parent::setUp();

        $this->symfonyWorkflowRegistry = $this->createMock(Registry::class);
    }

    public function testReturnWhetherTransitionCanBeApplied(): void
    {
        $subject = new \stdClass();
        $graphName = 'graph_name';
        $transition = 'transition';

        $workflow = $this->createMock(Workflow::class);
        $workflow
            ->expects($this->once())
            ->method('can')
            ->with($subject, $transition)
            ->willReturn(true)
        ;

        $this->symfonyWorkflowRegistry
            ->expects($this->once())
            ->method('get')
            ->with($subject, $graphName)
            ->willReturn($workflow)
        ;

        $this->createTestSubject()->can($subject, $graphName, $transition);
    }

    public function testAppliesTransition(): void
    {
        $subject = new \stdClass();
        $graphName = 'graph_name';
        $transition = 'transition';
        /** @var array<string, mixed> $context */
        $context = ['context'];

        $workflow = $this->createMock(Workflow::class);
        $workflow
            ->expects($this->once())
            ->method('apply')
            ->with($subject, $transition, $context)
        ;

        $this->symfonyWorkflowRegistry
            ->expects($this->once())
            ->method('get')
            ->with($subject, $graphName)
            ->willReturn($workflow)
        ;

        $this->createTestSubject()->apply($subject, $graphName, $transition, $context);
    }

    public function testReturnsEnabledTransitions(): void
    {
        $subject = new \stdClass();
        $graphName = 'graph_name';

        $workflow = $this->createMock(Workflow::class);
        $workflow
            ->expects($this->once())
            ->method('getEnabledTransitions')
            ->with($subject)
            ->willReturn([$this->createSampleTransition()])
        ;

        $this->symfonyWorkflowRegistry
            ->expects($this->once())
            ->method('get')
            ->with($subject, $graphName)
            ->willReturn($workflow)
        ;

        $enabledTransition = $this->createTestSubject()->getEnabledTransitions($subject, $graphName);

        $this->assertCount(1, $enabledTransition);
        $this->assertSame('transition', $enabledTransition[0]->getName());
        $this->assertSame(['from'], $enabledTransition[0]->getFroms());
        $this->assertSame(['to'], $enabledTransition[0]->getTos());
    }

    public function testConvertWorkflowExceptionsToCustomOnesOnCan(): void
    {
        $this->expectException(StateMachineExecutionException::class);

        $subject = new \stdClass();
        $graphName = 'graph_name';
        $transition = 'transition';

        $this->symfonyWorkflowRegistry->method('get')->willThrowException(new InvalidArgumentException());

        $this->createTestSubject()->can($subject, $graphName, $transition);
    }

    public function testConvertWorkflowExceptionsToCustomOnApply(): void
    {
        $this->expectException(StateMachineExecutionException::class);

        $subject = new \stdClass();
        $graphName = 'graph_name';
        $transition = 'transition';

        $this->symfonyWorkflowRegistry->method('get')->willThrowException(new InvalidArgumentException());

        $this->createTestSubject()->apply($subject, $graphName, $transition);
    }

    public function testConvertWorkflowExceptionsToCustomOnGetEnabledTransitions(): void
    {
        $this->expectException(StateMachineExecutionException::class);

        $subject = new \stdClass();
        $graphName = 'graph_name';

        $this->symfonyWorkflowRegistry->method('get')->willThrowException(new InvalidArgumentException());

        $this->createTestSubject()->getEnabledTransitions($subject, $graphName);
    }

    /**
     * @dataProvider returnsTransitionsToForGivenTransitionProvider
     */
    public function testReturnsTransitionsToForGivenTransition(?string $expectedToStateTransition, string $requestedTransition): void
    {
        $subject = new \stdClass();
        $graphName = 'graph_name';

        $someTransition = $this->createSampleTransition(
            name: 'some_transition',
            froms: ['from_1', 'from_2'],
            tos: ['to_1'],
        );
        $anotherTransition = $this->createSampleTransition(
            name: 'another_transition',
            froms: ['from_1', 'from_3'],
            tos: ['to_1', 'to_2'],
        );

        $workflow = $this->createMock(Workflow::class);
        $workflow->method('getEnabledTransitions')->with($subject)->willReturn([$someTransition, $anotherTransition]);

        $this->symfonyWorkflowRegistry->method('get')->with($subject, $graphName)->willReturn($workflow);

        $testSubject = $this->createTestSubject();

        $this->assertSame(
            $expectedToStateTransition,
            $testSubject->getTransitionToState($subject, $graphName, $requestedTransition),
        );
    }

    /**
     * @return iterable<string, mixed>
     */
    public static function returnsTransitionsToForGivenTransitionProvider(): iterable
    {
        yield 'it returns first transition for to_1' => [
            'expectedToStateTransition' => 'some_transition',
            'requestedTransition' => 'to_1',
        ];
        yield ' it returns second transition for to_2' => [
            'expectedToStateTransition' => 'another_transition',
            'requestedTransition' => 'to_2',
        ];
        yield 'it returns null for to_3' => [
            'expectedToStateTransition' => null,
            'requestedTransition' => 'to_3',
        ];
    }

    /**
     * @dataProvider returnsTransitionsFromForGivenTransitionProvider
     */
    public function testReturnsTransitionsFromForGivenTransition(?string $expectedFromStateTransition, string $requestedTransition): void
    {
        $subject = new \stdClass();
        $graphName = 'graph_name';

        $someTransition = $this->createSampleTransition(
            name: 'some_transition',
            froms: ['from_1', 'from_2'],
            tos: ['to_1'],
        );
        $anotherTransition = $this->createSampleTransition(
            name: 'another_transition',
            froms: ['from_1', 'from_3'],
            tos: ['to_1', 'to_2'],
        );

        $workflow = $this->createMock(Workflow::class);
        $workflow->method('getEnabledTransitions')->with($subject)->willReturn([$someTransition, $anotherTransition]);

        $this->symfonyWorkflowRegistry->method('get')->with($subject, $graphName)->willReturn($workflow);

        $testSubject = $this->createTestSubject();

        $this->assertSame(
            $expectedFromStateTransition,
            $testSubject->getTransitionFromState($subject, $graphName, $requestedTransition),
        );
    }

    /**
     * @return iterable<string, mixed>
     */
    public static function returnsTransitionsFromForGivenTransitionProvider(): iterable
    {
        yield 'it returns first transition for from_1' => [
            'expectedFromStateTransition' => 'some_transition',
            'requestedTransition' => 'from_1',
        ];
        yield 'it returns first transition for from_2' => [
            'expectedFromStateTransition' => 'some_transition',
            'requestedTransition' => 'from_2',
        ];
        yield 'it returns second transition for from_3' => [
            'expectedFromStateTransition' => 'another_transition',
            'requestedTransition' => 'from_3',
        ];
        yield 'it returns null for from_4' => [
            'expectedFromStateTransition' => null,
            'requestedTransition' => 'from_4',
        ];
    }

    private function createTestSubject(): SymfonyWorkflowAdapter
    {
        return new SymfonyWorkflowAdapter($this->symfonyWorkflowRegistry);
    }

    private function createSampleTransition(mixed ...$arguments): SymfonyWorkflowTransition
    {
        return new SymfonyWorkflowTransition(...array_replace(
            [
                'name' => 'transition',
                'froms' => ['from'],
                'tos' => ['to'],
            ],
            $arguments,
        ));
    }
}
