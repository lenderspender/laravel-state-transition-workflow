<?php

declare(strict_types=1);

namespace LenderSpender\StateTransitionWorkflow;

class TransitionWorkflowConfig
{
    /** @var string */
    public $field;

    /** @var array */
    private $allowedTransitions = [];

    public function __construct(string $field)
    {
        $this->field = $field;
    }

    /**
     * @param \LenderSpender\StateTransitionWorkflow\TransitionState|\LenderSpender\StateTransitionWorkflow\TransitionState[] $to
     *
     * @return \LenderSpender\StateTransitionWorkflow\TransitionWorkflowConfig
     */
    public function allowTransition(TransitionState $from, $to, string $workflowClass = null): self
    {
        if (is_array($to)) {
            foreach ($to as $toTransition) {
                $this->allowTransition($from, $toTransition, $workflowClass);
            }

            return $this;
        }

        if (! $workflowClass) {
            $workflowClass = new class() extends BaseWorkflow {
            };
        }

        $transitionKey = Transition::getTransitionKey($from, $to);
        $this->allowedTransitions[$transitionKey] = $workflowClass;

        return $this;
    }

    public function getWorkflow(Transition $transition): ?BaseWorkflow
    {
        $transition = $this->allowedTransitions[(string) $transition] ?? null;

        if (is_string($transition)) {
            return new $transition();
        }

        return $transition;
    }
}
