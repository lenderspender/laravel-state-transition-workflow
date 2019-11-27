<?php

declare(strict_types=1);

namespace LenderSpender\StateTransitionWorkflow\Tests\Stubs;

use LenderSpender\StateTransitionWorkflow\Transition;
use LenderSpender\StateTransitionWorkflow\Workflow;

class DeniedWorkflow extends Workflow
{
    public function isAllowed(Transition $transition): bool
    {
        return false;
    }
}
