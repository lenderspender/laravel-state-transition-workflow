<?php

declare(strict_types=1);

namespace LenderSpender\StateTransitionWorkflow;

use Illuminate\Contracts\Queue\ShouldQueue;
use LenderSpender\StateTransitionWorkflow\Exceptions\TransitionNotAllowedException;
use ReflectionClass;

trait HasStateTransitions
{
    /** @var \LenderSpender\StateTransitionWorkflow\TransitionWorkflowConfig[] */
    protected static array $stateFields = [];

    public static function bootHasStateTransitions(): void
    {
        $class = (new ReflectionClass(static::class))->newInstanceWithoutConstructor();
        $class->registerStateTransitions();
    }

    public function transitionStateTo(TransitionState $to, string $field = null): self
    {
        $transitionWorkflowConfig = $this->getTransitionWorkflowConfig($field);

        $field = $transitionWorkflowConfig->field;
        $transition = new Transition($this, $field, $this->{$field}, $to);
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
     * @return array<\LenderSpender\StateTransitionWorkflow\TransitionState>
     */
    public function getAvailableStateTransitions(?string $field = null): array
    {
        $transitionWorkflowConfig = $this->getTransitionWorkflowConfig($field);

        $currentState = $this->{$transitionWorkflowConfig->field};

        // @phpstan-ignore-next-line
        return array_values(array_map(function (array $transitions) {
            return $transitions['to'];
        }, $transitionWorkflowConfig->getAllowedTransitions($currentState)));
    }

    public function canTransitionTo(TransitionState $state, ?string $field = null): bool
    {
        return in_array($state, $this->getAvailableStateTransitions($field));
    }

    protected function addState(string $field): TransitionWorkflowConfig
    {
        $stateConfig = new TransitionWorkflowConfig($field);

        static::$stateFields[$field] = $stateConfig;

        return $stateConfig;
    }

    abstract protected function registerStateTransitions(): void;

    private function getTransitionWorkflowConfig(?string $field): TransitionWorkflowConfig
    {
        return static::$stateFields[$field] ?? array_values(static::$stateFields)[0];
    }
}
