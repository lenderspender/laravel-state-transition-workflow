<?php

declare(strict_types=1);

namespace LenderSpender\StateTransitionWorkflow\Tests\Stubs;

use LenderSpender\StateTransitionWorkflow\BaseWorkflow;
use LenderSpender\StateTransitionWorkflow\Transition;

class CustomWorkflow extends BaseWorkflow
{
    public function execute(Transition $transition): void
    {
        $transition->model->isTransitionedByCustomWorkflow = true;
    }
}
