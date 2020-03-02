<?php

declare(strict_types=1);

namespace LenderSpender\StateTransitionWorkflow;

use Illuminate\Database\Eloquent\Model;
use LenderSpender\StateTransitionWorkflow\Exceptions\TransitionNotAllowedException;

class Transition
{
    public string $field;
    public TransitionState $from;
    public TransitionState $to;
    private Model $model;
    private bool $isTransitioned = false;

    public function __construct(Model $model, string $field, TransitionState $from, TransitionState $to)
    {
        $this->model = $model;
        $this->field = $field;
        $this->from = $from;
        $this->to = $to;
    }

    public static function getTransitionKey(TransitionState $from, TransitionState $to): string
    {
        return "{$from}->{$to}";
    }

    public function execute(): void
    {
        if ($this->canBeTransitioned()) {
            $this->model->{$this->field} = $this->to;
            $this->model->save();

            $this->isTransitioned = true;

            return;
        }

        throw new TransitionNotAllowedException($this->model, $this);
    }

    public function canBeTransitioned(): bool
    {
        return $this->model->{$this->field} == $this->from && ! $this->isTransitioned;
    }

    public function __toString(): string
    {
        return self::getTransitionKey($this->from, $this->to);
    }
}
