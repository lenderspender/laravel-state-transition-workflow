# Laravel State Transitions Workflow 

This package adds state transitions workflows to your Laravel models.
Allowing you to specify transitions and how to act on those transitions.

To show you how to use this package, let's sketch the following scenario. 

A transaction can have three states `CREATED`, `FAILED` and `SUCCESS`. 
When transitioning between states you probably want to act on this transition e.g. send a success email. 
You probably only want to allow transitions from `SUCCESS` to `FAILED` and not the other way around.

The transaction model would look like:

```php
use LenderSpender\StateTransitionWorkflow\HasStateTransitions;

/**
 * @property \App\Enums\TransactionState $state
 */
class Transaction extends Model
{
    use HasStateTransitions;

    protected function registerStateTransitions(): void
    {
        $this->addState('status')
            ->allowTransition(TransactionState::CREATED(), TransactionState::SUCCESS(), TransactionSuccessfullWorkflow::class)
            ->allowTransition(TransactionState::CREATED(), TransactionState::FAILED());
    }
}
```

Here is what the TransactionState enum looks like:

```php
use LenderSpender\LaravelEnums\Enum;
use LenderSpender\StateTransitionWorkflow\TransitionState;

/**
 * @method static self CREATED()
 * @method static self SUCCESS()
 * @method static self FAILED()
 */
class FooStates extends Enum implements TransitionState
{
    private const CREATED = 'created';
    private const SUCCESS = 'success';
    private const FAILED = 'failed';
}
```

And here is what the TransactionSuccessfullWorkflow could look like:

```php
use Illuminate\Database\Eloquent\Model;
use LenderSpender\StateTransitionWorkflow\Transition;
use LenderSpender\StateTransitionWorkflow\Workflow;

class TransactionSuccessfullWorkflow extends Workflow
{
    public function __construct(FakeMailer $mailer)
    {
        $this->mailer = $mailer;
    }

    /**
     * @param \App\Models\Transaction $model
     */
    public function execute(Model $transaction, Transition $transition): void
    {
         $this->mailer->mail($transaction->email, 'Payment was sucessfull');
    }
}
```

And here is how you use it:

```php
$transaction = Transaction::find(1337);
$transaction->transitionStateTo(FooStates::SUCCESS());

$transaction->state == FooStates::SUCCESS; // true
```


## Installation

You can install the package via composer:


```bash
composer require lenderspender/laravel-state-transition-workflow
```

## Usage

The package provides a `HasStateTransitions` trait which you can use in any model where you want to support state.

## State management

**Registering state field**

To setup states for the `$status` attribute you should add the `HasStateTransitions` trait to the model and implement the `registerStateTransitions` method.  

```php
use Illuminate\Database\Eloquent\Model;
use LenderSpender\StateTransitionWorkflow\HasStateTransitions;

/**
 * @property \App\Enums\TransactionState $state
 */
class Transaction extends Model
{
    use HasStateTransitions;

    protected function registerStateTransitions(): void
    {
        $this->addState('status');
    }
}
```

**Adding allowed transitions**

Transitions are used to transition the state field for a model from one to another.
You need to specify which transitions are allowed and what workflow should be started on transition.
By default all transitions are not allowed, to allow transitions you should call `allowTransition` on the added state.

Single transition from `State::FROM()` to `State::TO()`

```php
class Transaction extends Model
{
    use HasStateTransitions;

    protected function registerStateTransitions(): void
    {
        $this->addState('status')
            ->allowTransitions(State::FROM(), State::TO());
    }
}
```

Allow transitions from `State::CREATED()` to `State::FAILED()` and `State::SUCCESS()`

```php
use Illuminate\Database\Eloquent\Model;
use LenderSpender\StateTransitionWorkflow\HasStateTransitions;

class Transaction extends Model
{
    use HasStateTransitions;

    protected function registerStateTransitions(): void
    {
        $this->addState('status')
            ->allowTransitions(State::CREATED(), [State::FAILED(), State::SUCCESS());
    }
}        
```

Allow transitions from `State::CREATED()` and `State::UPDATED()` to `State::FAILED()` and `State::SUCCESS()`

```php
use LenderSpender\StateTransitionWorkflow\HasStateTransitions;

class Transaction extends Model
{
    use HasStateTransitions;

    protected function registerStateTransitions(): void
    {
        $this->addState('status')
            ->allowTransitions([State::CREATED(), State::UPDATED()], [State::FAILED(), State::SUCCESS()]);
    }
}
```

**Using transitions**

Transitions can be used by calling the `transitionStateTo` method on the model.

```php
$transaction->transitionStateTo(State::SUCCESS());
```

By default the method uses the first registered state. When you've added multiple state fields you should specify which field to use.

```php
$transaction->transitionStateTo(State::SUCCESS(), 'status');
```

When a state transitions is not allowed a `LenderSpender\StateTransitionWorkflow\Exceptions\TransitionNotAllowedException` is thrown.

**Listing allowed state transitions**

To know what allowed state transitions can be performed you could call the `getAvailableStateTransitions` on the model.

```php
$transaction->getAvailableStateTransitions();
```

By default the method uses the first registered state. When you've added multiple state fields you should specify which field to use.

```php
$transaction->getAvailableStateTransitions('status');
```


## State workflows

When transitioning a model from one state to another you sometimes want to act on this transition. Or even prevent the transition from happening. That's where state workflows come into place.

**Creating a workflow**

Automatically handle the transition after workflow execution

```php
use Illuminate\Database\Eloquent\Model;
use LenderSpender\StateTransitionWorkflow\Transition;
use LenderSpender\StateTransitionWorkflow\Workflow;

class PaidWorkflow extends Workflow
{
    /**
     * @param \App\Model\Transaction $model
     */
    public function execute(Model $transaction, Transition $transition): void
    {
         dump('This is executed before the transition');
    }
}
```

Handle the transition in the workflow

```php
use Illuminate\Database\Eloquent\Model;
use LenderSpender\StateTransitionWorkflow\Transition;
use LenderSpender\StateTransitionWorkflow\Workflow;

class PaidWorkflow extends Workflow
{
    /**
     * @param \App\Model\Transaction $model
     */
    public function execute(Model $transaction, Transition $transition): void
    {
         dump('This is executed before the transition');
         $transition->execute();
         dump('This is executed after the transition');
    }
}
```

**Queuing transitions**

When you want to perform some heavy actions before or after the transition you could queue the transition by implementing the `ShouldQueue` interface on the workflow.


```php
use Illuminate\Contracts\Queue\ShouldQueue;
use LenderSpender\StateTransitionWorkflow\Workflow;

class PaidWorkflow extends Workflow implements ShouldQueue
{
}
```

**Preventing transitions**

Transitions can be prevented when you override the `isAllowed` method and return false in the workflow.

```php
use LenderSpender\StateTransitionWorkflow\Workflow;

class PaidWorkflow extends Workflow
{
    public function isAllowed(Transition $transition): bool
    {
        return false;
    }
}
```

**Registering a workflow to a state transition**

```php
class Transaction extends Model
{
    use HasStateTransitions;

    protected function registerStateTransitions(): void
    {
        $this->addState('status')
            ->allowTransitions(State::CREATED(), State::SUCCESS(), PaidWorkflow::class);
    }
}
```
