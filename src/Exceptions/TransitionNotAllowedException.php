<?php

declare(strict_types=1);

namespace LenderSpender\StateTransitionWorkflow\Exceptions;

use Illuminate\Database\Eloquent\Model;
use LenderSpender\StateTransitionWorkflow\Transition;
use RuntimeException;

class TransitionNotAllowedException extends RuntimeException
{
    public function __construct(Model $model, Transition $transition)
    {
        $model = get_class($model);

        parent::__construct("Transition {$transition} is not allowed on {$model}");
    }
}
