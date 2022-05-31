<?php

namespace App\Http\Controllers\User;

use App\Aggregates\AccountAggregate;
use App\Http\Controllers\Controller;
use App\Models\StoredEvent;
use App\Services\AccountService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class UserAccountController extends Controller
{
    public function index()
    {
        $account = Auth::user()->account;
        $transactionCount = 0;
        $history = collect([]);

        if ($account) {
            //$transactionCount = TransactionCount::where('account_uuid', $account->uuid)->value('count');
            $transactionCount = $account->transactionCount()->value('count');

            $history = StoredEvent::query()
                ->history($account->uuid)
                ->get();
        }

        return view('dashboard', [
            'account'          => $account,
            'transactionCount' => $transactionCount ?? 0,
            'history'          => $history ?? null,
        ]);
    }

    public function store(Request $request)
    {
        $attributes = $request->only('name');

        if (Auth::user()->account()->exists()) {
            session()->flash('status', ['type' => 'warning', 'message' => 'Account Exists']);

            return redirect()->back();
        }

        $uuid = Str::uuid();

        AccountAggregate::retrieve($uuid)
            ->createAccount($request->name, Auth::id())
            ->persist();

        session()->flash('status', ['type' => 'success', 'message' => 'AccountService created']);

        return redirect()->route('dashboard');
    }

    public function addMoney(Request $request)
    {
        $accountAggregate = AccountService::getAggregateRoot();

        $accountAggregate->addMoney(abs($request->amount))->persist();

        session()->flash('status', ['type' => 'success', 'message' => 'Money Deposited']);

        return redirect()->route('dashboard');

    }

    public function subtractMoney(Request $request)
    {
        $accountAggregate = AccountService::getAggregateRoot();

        $accountAggregate->subtractMoney(abs($request->amount))->persist();

        session()->flash('status', ['type' => 'success', 'message' => 'Money Withdrew']);

        return redirect()->route('dashboard');

    }
}
