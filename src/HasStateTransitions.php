<?php

declare(strict_types=1);

namespace LenderSpender\StateTransitionWorkflow;

use Illuminate\Contracts\Queue\ShouldQueue;
use LenderSpender\StateTransitionWorkflow\Exceptions\TransitionNotAllowedException;

trait HasStateTransitions
{
    /** @var \LenderSpender\StateTransitionWorkflow\TransitionWorkflowConfig[]|null */
    protected static $stateFields = null;

    public function transitionTo(TransitionState $to, string $field = null): self
    {
        $this->registerStateTransitions();

        /** @var \LenderSpender\StateTransitionWorkflow\TransitionWorkflowConfig $stateConfig */
        $stateConfig = static::$stateFields[$field] ?? array_values(static::$stateFields)[0];

        $transition = new Transition($this, $stateConfig->field, $this->{$stateConfig->field}, $to);
        $workflow = $stateConfig->getWorkflow($transition);

        if (! $workflow || ! $workflow->isAllowed($transition)) {
            throw new TransitionNotAllowedException($transition);
        }

        if ($workflow instanceof ShouldQueue) {
            $workflow->onQueue()->execute($transition);
        } else {
            $workflow->execute($transition);

            if ($transition->canBeTransitioned()) {
                $transition->execute();
            }
        }

        return $this;
    }

    protected function addState(string $field): TransitionWorkflowConfig
    {
        $stateConfig = new TransitionWorkflowConfig($field);

        static::$stateFields[$field] = $stateConfig;

        return $stateConfig;
    }

    abstract protected function registerStateTransitions(): void;
}
