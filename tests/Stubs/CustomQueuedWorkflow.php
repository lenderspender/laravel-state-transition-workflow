<?php

declare(strict_types=1);

namespace LenderSpender\StateTransitionWorkflow\Tests\Stubs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Model;
use LenderSpender\StateTransitionWorkflow\Transition;
use LenderSpender\StateTransitionWorkflow\Workflow;

class CustomQueuedWorkflow extends Workflow implements ShouldQueue
{
    public function execute(Model $model, Transition $transition): void
    {
        ++$model->timesTransitionedByQueuedWorkflow;
        $transition->execute();
    }
}
