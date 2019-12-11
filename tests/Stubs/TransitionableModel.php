<?php

declare(strict_types=1);

namespace LenderSpender\StateTransitionWorkflow\Tests\Stubs;

use Illuminate\Database\Eloquent\Model;
use LenderSpender\StateTransitionWorkflow\HasStateTransitions;

class TransitionableModel extends Model
{
    use HasStateTransitions;

    public $isTransitionedByCustomWorkflow = false;
    public $timesTransitionedByQueuedWorkflow = 0;

    protected $enums = [
        'status' => FooStates::class,
    ];

    public function save(array $options = [])
    {
        return true;
    }

    protected function registerStateTransitions(): void
    {
        $this->addState('status')
            ->allowTransition(FooStates::FIRST(), FooStates::SECOND())
            ->allowTransition(FooStates::SECOND(), [FooStates::FIRST(), FooStates::WITH_CUSTOM_WORKFLOW_CLASS()])
            ->allowTransition([FooStates::MULTIPLE1(), FooStates::MULTIPLE2()], [FooStates::FIRST(), FooStates::SECOND()])
            ->allowTransition(FooStates::FIRST(), FooStates::WITH_CUSTOM_WORKFLOW_CLASS(), CustomWorkflow::class)
            ->allowTransition(FooStates::FIRST(), FooStates::WITH_CUSTOM_QUEUED_WORKFLOW_CLASS(), CustomQueuedWorkflow::class)
            ->allowTransition(FooStates::FIRST(), FooStates::WITH_DENIED_WORKFLOW_CLASS(), DeniedWorkflow::class);
    }
}
