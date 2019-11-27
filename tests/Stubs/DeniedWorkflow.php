<?php

declare(strict_types=1);

namespace LenderSpender\StateTransitionWorkflow\Tests\Stubs;

use LenderSpender\StateTransitionWorkflow\BaseWorkflow;
use LenderSpender\StateTransitionWorkflow\Transition;

class DeniedWorkflow extends BaseWorkflow
{
    public function isAllowed(Transition $transition): bool
    {
        return false;
    }
}
