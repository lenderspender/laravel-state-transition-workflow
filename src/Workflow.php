<?php

declare(strict_types=1);

namespace LenderSpender\StateTransitionWorkflow;

use Illuminate\Database\Eloquent\Model;
use Spatie\QueueableAction\QueueableAction;

abstract class Workflow
{
    use QueueableAction;

    public function isAllowed(Transition $transition): bool
    {
        return true;
    }

    public function execute(Model $model, Transition $transition): void
    {
        $transition->execute();
    }
}
