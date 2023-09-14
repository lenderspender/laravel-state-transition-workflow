<?php

declare(strict_types=1);

namespace LenderSpender\StateTransitionWorkflow\Tests\Stubs;

use BackedEnum;
use Illuminate\Database\Eloquent\Model;
use LenderSpender\StateTransitionWorkflow\HasStateTransitions;

/**
 * @property \LenderSpender\StateTransitionWorkflow\Tests\Stubs\FooStates $status
 */
class TransitionableModel extends Model
{
    use HasStateTransitions;

    public bool $isTransitionedByCustomWorkflow = false;
    public int $timesTransitionedByQueuedWorkflow = 0;

    /** @var array<string, class-string<BackedEnum>> */
    protected $casts = [
        'status' => FooStates::class,
    ];

    /**
     * @param array<string, mixed> $attributes
     * @param array<string, mixed> $options
     */
    public function update(array $attributes = [], array $options = []): bool
    {
        $this->fill($attributes);

        return true;
    }

    /**
     * @param array<string, mixed> $options
     */
    public function save(array $options = []): bool
    {
        return true;
    }

    protected function registerStateTransitions(): void
    {
        $this->addState('status')
            ->allowTransition(FooStates::FIRST, FooStates::SECOND)
            ->allowTransition(FooStates::SECOND, [FooStates::FIRST, FooStates::WITH_CUSTOM_WORKFLOW_CLASS])
            ->allowTransition([FooStates::MULTIPLE1, FooStates::MULTIPLE2], [FooStates::FIRST, FooStates::SECOND])
            ->allowTransition(FooStates::FIRST, FooStates::WITH_CUSTOM_WORKFLOW_CLASS, CustomWorkflow::class)
            ->allowTransition(FooStates::FIRST, FooStates::WITH_CUSTOM_QUEUED_WORKFLOW_CLASS, CustomQueuedWorkflow::class)
            ->allowTransition(FooStates::FIRST, FooStates::WITH_DENIED_WORKFLOW_CLASS, DeniedWorkflow::class);
    }
}
