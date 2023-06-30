<?php

declare(strict_types=1);

namespace LenderSpender\StateTransitionWorkflow\Tests;

use Illuminate\Support\Facades\Queue;
use LenderSpender\StateTransitionWorkflow\Exceptions\TransitionNotAllowedException;
use LenderSpender\StateTransitionWorkflow\Tests\Stubs\CustomQueuedWorkflow;
use LenderSpender\StateTransitionWorkflow\Tests\Stubs\FooStates;
use LenderSpender\StateTransitionWorkflow\Tests\Stubs\TransitionableModel;
use Spatie\QueueableAction\ActionJob;

class TransitionToTest extends TestCase
{
    public function test_model_can_be_transitioned(): void
    {
        $model = new TransitionableModel();
        $model->status = FooStates::FIRST;

        $model->transitionStateTo(FooStates::SECOND);

        self::assertEquals(FooStates::SECOND, $model->status);
    }

    public function test_model_can_be_transitioned_from_multiple_states(): void
    {
        $model1 = new TransitionableModel();
        $model1->status = FooStates::MULTIPLE1;

        $model2 = new TransitionableModel();
        $model2->status = FooStates::MULTIPLE1;

        $model1->transitionStateTo(FooStates::FIRST);
        $model2->transitionStateTo(FooStates::SECOND);

        self::assertEquals(FooStates::FIRST, $model1->status);
        self::assertEquals(FooStates::SECOND, $model2->status);
    }

    public function test_model_cannot_be_transitioned(): void
    {
        $model = new TransitionableModel();
        $model->status = FooStates::SECOND;

        try {
            $model->transitionStateTo(FooStates::SECOND);
        } catch (TransitionNotAllowedException $e) {
            self::assertSame('Transition second->second is not allowed on ' . TransitionableModel::class, $e->getMessage());

            return;
        }

        self::fail('Not allowed transition should throw exception');
    }

    public function test_custom_workflow_is_executed(): void
    {
        $model = new TransitionableModel();
        $model->status = FooStates::FIRST;

        $model->transitionStateTo(FooStates::WITH_CUSTOM_WORKFLOW_CLASS);

        self::assertTrue($model->isTransitionedByCustomWorkflow);
        self::assertEquals(FooStates::WITH_CUSTOM_WORKFLOW_CLASS, $model->status);
    }

    public function test_model_cannot_be_transition_based_on_workflow_class(): void
    {
        $model = new TransitionableModel();
        $model->status = FooStates::FIRST;

        try {
            $model->transitionStateTo(FooStates::WITH_DENIED_WORKFLOW_CLASS);
        } catch (TransitionNotAllowedException $e) {
            self::assertSame('Transition first->with_denied_workflow_class is not allowed on ' . TransitionableModel::class, $e->getMessage());

            return;
        }

        self::fail('Not allowed transition should throw exception');
    }

    public function test_workflow_can_be_queued(): void
    {
        Queue::fake();
        $model = new TransitionableModel();
        $model->status = FooStates::FIRST;

        $model->transitionStateTo(FooStates::WITH_CUSTOM_QUEUED_WORKFLOW_CLASS);

        Queue::assertPushed(ActionJob::class, function (ActionJob $actionJob) {
            self::assertSame(CustomQueuedWorkflow::class, $actionJob->displayName());
            $actionJob->handle();

            return true;
        });
        self::assertEquals(1, $model->timesTransitionedByQueuedWorkflow);
        self::assertEquals(FooStates::WITH_CUSTOM_QUEUED_WORKFLOW_CLASS, $model->status);
    }

    public function test_transition_can_only_be_executed_once(): void
    {
        Queue::fake();
        $model = new TransitionableModel();
        $model->status = FooStates::FIRST;

        $model->transitionStateTo(FooStates::WITH_CUSTOM_QUEUED_WORKFLOW_CLASS);
        $model->transitionStateTo(FooStates::WITH_CUSTOM_QUEUED_WORKFLOW_CLASS);

        Queue::assertPushed(ActionJob::class, function (ActionJob $actionJob) {
            try {
                $actionJob->handle();
            } catch (TransitionNotAllowedException $e) {
                self::assertSame('Transition first->with_custom_queued_workflow_class is not allowed on ' . TransitionableModel::class, $e->getMessage());

                return true;
            }

            return false;
        });
    }
}
