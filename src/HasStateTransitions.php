<?php

declare(strict_types=1);

namespace LenderSpender\StateTransitionWorkflow;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Attributes\Boot;
use LenderSpender\StateTransitionWorkflow\Exceptions\TransitionNotAllowedException;
use UnexpectedValueException;
use function PHPUnit\Framework\assertInstanceOf;

trait HasStateTransitions
{
    /** @var TransitionWorkflowConfig[] */
    protected static array $stateFields = [];

    #[Boot]
    public static function bootHasStateTransitions(): void
    {
        static::registerStateTransitions();
    }

    protected static function addState(string $field): TransitionWorkflowConfig
    {
        $stateConfig = new TransitionWorkflowConfig($field);

        static::$stateFields[$field] = $stateConfig;

        return $stateConfig;
    }

    public function transitionStateTo(TransitionState $to, ?string $field = null): self
    {
        $transitionWorkflowConfig = $this->getTransitionWorkflowConfig($field);

        $field = $transitionWorkflowConfig->field;
        $transition = new Transition($this, $field, $this->getState($field), $to);
        $workflow = $transitionWorkflowConfig->getWorkflow($transition);

        if (! $workflow || ! $workflow->isAllowed($transition)) {
            throw new TransitionNotAllowedException($this, $transition);
        }

        if ($workflow instanceof ShouldQueue) {
            $workflow->onQueue()->execute($this, $transition);
        } else {
            $workflow->execute($this, $transition);

            if ($transition->canBeTransitioned()) {
                $transition->execute();
            }
        }

        return $this;
    }

    /**
     * @return array<int, TransitionState|Workflow|string>
     */
    public function getAvailableStateTransitions(?string $field = null): array
    {
        $transitionWorkflowConfig = $this->getTransitionWorkflowConfig($field);

        $currentState = $this->getState($transitionWorkflowConfig->field);

        return array_values(array_map(function (array $transitions) {
            return $transitions['to'];
        }, $transitionWorkflowConfig->getAllowedTransitions($currentState)));
    }

    public function canTransitionTo(TransitionState $state, ?string $field = null): bool
    {
        return in_array($state, $this->getAvailableStateTransitions($field));
    }

    abstract protected static function registerStateTransitions(): void;

    private function getTransitionWorkflowConfig(?string $field): TransitionWorkflowConfig
    {
        if ($field !== null && isset(static::$stateFields[$field])) {
            return static::$stateFields[$field];
        }

        return array_values(static::$stateFields)[0];
    }

    private function getState(string $field): TransitionState
    {
        $state = $this->{$field};

        assert(
            TransitionState::class instanceof $state,
            sprintf('State field [%s] on [%s] must hold a %s, %s given.', $field, static::class, TransitionState::class, get_debug_type($state))
        );

        return $state;
    }
}
