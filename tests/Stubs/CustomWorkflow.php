<?php

declare(strict_types=1);

namespace LenderSpender\StateTransitionWorkflow\Tests\Stubs;

use Illuminate\Database\Eloquent\Model;
use LenderSpender\StateTransitionWorkflow\Transition;
use LenderSpender\StateTransitionWorkflow\Workflow;

class CustomWorkflow extends Workflow
{
    /**
     * @param \LenderSpender\StateTransitionWorkflow\Tests\Stubs\TransitionableModel $model
     */
    public function execute(Model $model, Transition $transition): void
    {
        $model->isTransitionedByCustomWorkflow = true;
    }
}
