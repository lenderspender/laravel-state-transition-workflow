<?php

declare(strict_types=1);

namespace LenderSpender\StateTransitionWorkflow;

/**
 * @internal
 */
class TransitionWorkflowConfig
{
    public string $field;

    /** @var array<string, array<string, array<string, class-string<\LenderSpender\StateTransitionWorkflow\Workflow|\LenderSpender\StateTransitionWorkflow\TransitionState>>>> */
    private array $allowedTransitions = [];

    public function __construct(string $field)
    {
        $this->field = $field;
    }

    /**
     * @param \LenderSpender\StateTransitionWorkflow\TransitionState|iterable<\LenderSpender\StateTransitionWorkflow\TransitionState> $froms
     * @param \LenderSpender\StateTransitionWorkflow\TransitionState|iterable<\LenderSpender\StateTransitionWorkflow\TransitionState> $tos
     * @param class-string<\LenderSpender\StateTransitionWorkflow\Workflow>|null                                                      $workflowClass
     *
     * @return \LenderSpender\StateTransitionWorkflow\TransitionWorkflowConfig
     */
    public function allowTransition($froms, $tos, ?string $workflowClass = null): self
    {
        $tos = is_iterable($tos) ? $tos : [$tos];
        $froms = is_iterable($froms) ? $froms : [$froms];

        if (! $workflowClass) {
            $workflowClass = new class() extends Workflow {
            };
        }

        // @phpstan-ignore-next-line
        collect($froms)->each(function (TransitionState $from) use ($tos, $workflowClass) {
            // @phpstan-ignore-next-line
            collect($tos)->each(function (TransitionState $transition) use ($from, $workflowClass) {
                // @phpstan-ignore-next-line
                $this->allowedTransitions[$from->getValue()][$transition->getValue()] = [
                    'workflow' => $workflowClass,
                    'to' => $transition,
                ];
            });
        });

        return $this;
    }

    public function getWorkflow(Transition $transition): ?Workflow
    {
        $workflow = $this->getAllowedTransitions($transition->from)[$transition->to->getValue()]['workflow'] ?? null;

        if (is_string($workflow)) {
            // @phpstan-ignore-next-line
            return app($workflow);
        }

        // @phpstan-ignore-next-line
        return $workflow;
    }

    /**
     * @return array<string, array<string, string|\LenderSpender\StateTransitionWorkflow\Workflow|\LenderSpender\StateTransitionWorkflow\TransitionState>>|array{}
     */
    public function getAllowedTransitions(TransitionState $from): array
    {
        return $this->allowedTransitions[$from->getValue()] ?? [];
    }
}
