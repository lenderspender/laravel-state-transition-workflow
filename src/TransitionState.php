<?php

declare(strict_types=1);

namespace LenderSpender\StateTransitionWorkflow;

interface TransitionState
{
    public function getValue(): string;
}
