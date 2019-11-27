<?php

declare(strict_types=1);

namespace LenderSpender\StateTransitionWorkflow;

use Spatie\QueueableAction\QueueableAction;

abstract class BaseWorkflow
{
    use QueueableAction;

    public function isAllowed(Transition $transition): bool
    {
        return true;
    }

    public function execute(Transition $transition): void
    {
        $transition->execute();
    }
}
