<?php

namespace App\Exceptions;

use Exception;

class CouldNotSubtractMoney extends \DomainException
{
    public static function notEnoughFunds(float $amount): self
    {
        throw new static("Couldn't withdraw amount {$amount}. ".'Account balance should be above -5000');
    }
}
