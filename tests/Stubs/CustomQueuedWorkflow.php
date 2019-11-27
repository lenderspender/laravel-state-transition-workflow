<?php

declare(strict_types=1);

namespace LenderSpender\StateTransitionWorkflow\Tests\Stubs;

use Illuminate\Contracts\Queue\ShouldQueue;
use LenderSpender\StateTransitionWorkflow\BaseWorkflow;
use LenderSpender\StateTransitionWorkflow\Transition;

class CustomQueuedWorkflow extends BaseWorkflow implements ShouldQueue
{
    public function execute(Transition $transition): void
    {
        ++$transition->model->timesTransitionedByQueuedWorkflow;
        $transition->execute();
    }
}
