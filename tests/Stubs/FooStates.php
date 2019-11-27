<?php

declare(strict_types=1);

namespace LenderSpender\StateTransitionWorkflow\Tests\Stubs;

use LenderSpender\LaravelEnums\Enum;
use LenderSpender\StateTransitionWorkflow\TransitionState;

/**
 * @method static self FIRST()
 * @method static self SECOND()
 * @method static self THIRD()
 * @method static self WITH_CUSTOM_WORKFLOW_CLASS()
 * @method static self WITH_CUSTOM_QUEUED_WORKFLOW_CLASS()
 * @method static self WITH_DENIED_WORKFLOW_CLASS()
 */
class FooStates extends Enum implements TransitionState
{
    private const FIRST = 'first';
    private const SECOND = 'second';
    private const THIRD = 'third';
    private const WITH_CUSTOM_WORKFLOW_CLASS = 'with_custom_workflow_class';
    private const WITH_CUSTOM_QUEUED_WORKFLOW_CLASS = 'with_custom_queued_workflow_class';
    private const WITH_DENIED_WORKFLOW_CLASS = 'with_denied_workflow_class';
}
