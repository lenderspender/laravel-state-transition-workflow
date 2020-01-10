<?php

declare(strict_types=1);

namespace LenderSpender\StateTransitionWorkflow\Tests\Stubs;

use Illuminate\Database\Eloquent\Model;
use LenderSpender\StateTransitionWorkflow\HasStateTransitions;

/**
 * @property \LenderSpender\StateTransitionWorkflow\Tests\Stubs\FooStates $status
 */
class TransitionableModel extends Model
{
    use HasStateTransitions;

    /** @var bool */
    public $isTransitionedByCustomWorkflow = false;

    /** @var int */
    public $timesTransitionedByQueuedWorkflow = 0;

    protected $enums = [
        'status' => FooStates::class,
    ];

    /**
     * @param array<string, mixed> $attributes
     * @param array<string, mixed> $options
     */
    public function update(array $attributes = [], array $options = []): bool
    {
        $this->fill($attributes);
    }
  
    /**
     * @param array<string, mixed> $options
     */
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
