<?php

declare(strict_types=1);

namespace LenderSpender\StateTransitionWorkflow;

use Illuminate\Database\Eloquent\Model;
use LenderSpender\StateTransitionWorkflow\Exceptions\TransitionNotAllowedException;

/**
 * @internal
 */
class Transition
{
    /** @var \Illuminate\Database\Eloquent\Model */
    public $model;

    /** @var string */
    public $field;

    /** @var \LenderSpender\StateTransitionWorkflow\TransitionState */
    public $from;

    /** @var \LenderSpender\StateTransitionWorkflow\TransitionState */
    public $to;

    /** @var bool */
    private $isTransitioned = false;

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
            $this->model->update([
                $this->field => $this->to,
            ]);

            $this->isTransitioned = true;

            return;
        }

        throw new TransitionNotAllowedException($this);
    }

    public function canBeTransitioned(): bool
    {
        return $this->model->{$this->field} === $this->from && ! $this->isTransitioned;
    }

    public function __toString(): string
    {
        return self::getTransitionKey($this->from, $this->to);
    }
}
