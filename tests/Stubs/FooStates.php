<?php

declare(strict_types=1);

namespace LenderSpender\StateTransitionWorkflow\Tests\Stubs;

use LenderSpender\StateTransitionWorkflow\TransitionState;

enum FooStates: string implements TransitionState
{
    case FIRST = 'first';
    case SECOND = 'second';
    case THIRD = 'third';
    case WITH_CUSTOM_WORKFLOW_CLASS = 'with_custom_workflow_class';
    case WITH_CUSTOM_QUEUED_WORKFLOW_CLASS = 'with_custom_queued_workflow_class';
    case WITH_DENIED_WORKFLOW_CLASS = 'with_denied_workflow_class';
    case MULTIPLE1 = 'multiple1';
    case MULTIPLE2 = 'multiple2';

    public function getValue(): string
    {
        return $this->value;
    }
}
