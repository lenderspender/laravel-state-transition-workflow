<?php

declare(strict_types=1);

namespace LenderSpender\StateTransitionWorkflow\Tests;

use LenderSpender\StateTransitionWorkflow\Tests\Stubs\FooStates;
use LenderSpender\StateTransitionWorkflow\Tests\Stubs\TransitionableModel;

class AvailableStateTransitionsTest extends TestCase
{
    public function test_can_get_available_state_transitions(): void
    {
        $model = new TransitionableModel();
        $model->status = FooStates::SECOND();

        $availableStates = $model->getAvailableStateTransitions();

        self::assertEquals([
            FooStates::FIRST(),
            FooStates::WITH_CUSTOM_WORKFLOW_CLASS(),
        ], $availableStates);
    }

    public function test_can_transition_to(): void
    {
        $model = new TransitionableModel();
        $model->status = FooStates::SECOND();

        $canTransitionTo = $model->canTransitionTo(FooStates::FIRST());
        $cannotTransitionTo = $model->canTransitionTo(FooStates::SECOND());

        self::assertTrue($canTransitionTo);
        self::assertFalse($cannotTransitionTo);
    }
}
