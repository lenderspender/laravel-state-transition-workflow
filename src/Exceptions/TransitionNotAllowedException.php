<?php

declare(strict_types=1);

namespace LenderSpender\StateTransitionWorkflow\Exceptions;

use LenderSpender\StateTransitionWorkflow\Transition;
use RuntimeException;

class TransitionNotAllowedException extends RuntimeException
{
    public function __construct(Transition $transition)
    {
        $model = get_class($transition->model);

        parent::__construct("Transition {$transition} is not allowed on {$model}");
    }
}
