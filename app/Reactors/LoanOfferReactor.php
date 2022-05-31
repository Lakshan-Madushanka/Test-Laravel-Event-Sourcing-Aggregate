<?php

namespace App\Reactors;

use App\Events\MoreMoneyNeeded;
use App\Notifications\LoanProposed;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;
use Spatie\EventSourcing\EventHandlers\Reactors\Reactor;

class LoanOfferReactor extends Reactor implements ShouldQueue
{
    public function __invoke(MoreMoneyNeeded $event)
    {
        Notification::route('mail', [Auth::user()->email => Auth::user()->name])
            ->notify(new LoanProposed());
    }
}
