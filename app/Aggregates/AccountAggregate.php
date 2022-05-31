<?php

namespace App\Aggregates;

use App\Events\AccountCreated;
use App\Events\AccountLimitHit;
use App\Events\MoneyAdded;
use App\Events\MoneySubtracted;
use App\Events\MoreMoneyNeeded;
use App\Exceptions\CouldNotSubtractMoney;
use Spatie\EventSourcing\AggregateRoots\AggregateRoot;
use Throwable;

class AccountAggregate extends AggregateRoot
{
    public $balance = 0;
    public $adjacentAccountLimitHit = 0;
    private $accountLimit = -5000;

    public function createAccount(string $name, string $userId)
    {
        $this->recordThat(new AccountCreated($name, $userId));

        return $this;
    }

    public function addMoney(float $amount)
    {
        $this->recordThat(new MoneyAdded($amount));

        return $this;
    }

    /**
     * @throws Throwable
     */
    public function subtractMoney(float $amount)
    {
        if (! $this->hasSufficientBalanceToSubtract($amount)) {
            $this->recordThat(new AccountLimitHit($amount));

            if ($this->needsMoreMoney()) {
                $this->recordThat(new MoreMoneyNeeded());
            }

            $this->persist();

            throw CouldNotSubtractMoney::notEnoughFunds($amount);
        }

        $this->recordThat(new MoneySubtracted($amount));

        return $this;
    }

    public function hasSufficientBalanceToSubtract(float $amount)
    {
        return $this->balance - $amount >= -5000;
    }

    public function needsMoreMoney()
    {
        return $this->adjacentAccountLimitHit > 3;
    }

    public function applyMoneyAdded(MoneyAdded $event)
    {
        $this->adjacentAccountLimitHit = 0;
        $this->balance += $event->amount;
    }

    public function applyMoneySubtracted(MoneySubtracted $event)
    {
        $this->adjacentAccountLimitHit = 0;
        $this->balance -= $event->amount;
    }

    public function applyOnAccountLimitHit(AccountLimitHit $event)
    {
        $this->adjacentAccountLimitHit++;
    }
}
