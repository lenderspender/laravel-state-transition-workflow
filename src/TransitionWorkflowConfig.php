<?php

declare(strict_types=1);

namespace LenderSpender\StateTransitionWorkflow;

/**
 * @internal
 */
class TransitionWorkflowConfig
{
    public string $field;

    /** @var array<string, array<string, array>> */
    private array $allowedTransitions = [];

    public function __construct(string $field)
    {
        $this->field = $field;
    }

    /**
     * @param \LenderSpender\StateTransitionWorkflow\TransitionState|iterable<\LenderSpender\StateTransitionWorkflow\TransitionState> $froms
     * @param \LenderSpender\StateTransitionWorkflow\TransitionState|iterable<\LenderSpender\StateTransitionWorkflow\TransitionState> $tos
     * @param null|class-string<\LenderSpender\StateTransitionWorkflow\Workflow>                                                      $workflowClass
     *
     * @return \LenderSpender\StateTransitionWorkflow\TransitionWorkflowConfig
     */
    public function allowTransition($froms, $tos, string $workflowClass = null): self
    {
        $tos = is_iterable($tos) ? $tos : [$tos];
        $froms = is_iterable($froms) ? $froms : [$froms];

        if (! $workflowClass) {
            $workflowClass = new class() extends Workflow {
            };
        }

        collect($froms)->each(function (TransitionState $from) use ($tos, $workflowClass) {
            collect($tos)->each(function (TransitionState $transition) use ($from, $workflowClass) {
                $this->allowedTransitions[(string) $from][(string) $transition] = [
                    'workflow' => $workflowClass,
                    'to' => $transition,
                ];
            });
        });

        return $this;
    }

    public function getWorkflow(Transition $transition): ?Workflow
    {
        $workflow = $this->getAllowedTransitions($transition->from)[(string) $transition->to]['workflow'] ?? null;

        if (is_string($workflow)) {
            return app($workflow);
        }

        return $workflow;
    }

    /**
     * @return array<string, array>
     */
    public function getAllowedTransitions(TransitionState $from): array
    {
        return $this->allowedTransitions[(string) $from] ?? [];
    }
}
