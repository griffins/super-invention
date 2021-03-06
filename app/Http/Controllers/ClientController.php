<?php

namespace App\Http\Controllers;

use App\Account;
use App\Client;
use App\Notifications\TransactionRequest;
use App\Request;
use App\SupportTicket;
use App\Transaction;
use App\User;
use Carbon\Carbon;
use function request;

class ClientController extends Controller
{
    /**
     * HomeController constructor.
     */
    public function __construct()
    {
        $this->middleware('auth:admin,client');
    }

    public function index(Client $client)
    {
        if (user()->role == 'admin' || user()->id == $client->id) {
            $types = ['General', 'Dispute', 'Financial'];
            $periods = (object)[
                (object)[
                    'name' => 'Today',
                    'start' => now()->startOfDay(),
                    'end' => now()->endOfDay()],
            ];
            $account = Account::query()->findOrFail(cache('default_wallet'));
            return view('client.profile', compact('client', 'periods', 'types', 'account'));
        } else {
            abort(404);
        }
    }

    public function profit()
    {
        Client::updateBalances2(Account::query()->findOrFail(request('account_id')), request('value_type'), request('amount'), Carbon::parse(request('date')));
        return back()->withMessage('Successful');
    }

    public function openTicket(Client $client)
    {
        $types = ['General', 'Dispute', 'Financial'];

        request()->validate([
            'type' => 'required|in:' . implode(',', $types),
            'subject' => 'required',
            'narration' => 'required',
        ]);
        $ticket = new SupportTicket(request()->only('type', 'subject', 'narration'));
        $client->tickets()->save($ticket);
        session()->put("message", "Ticket Opened");
        return redirect(route('client', compact('client')));
    }

    public function transaction(Client $client)
    {
        if (user()->role == 'admin') {
            $account = Account::query()->findOrFail(cache('default_wallet'));
            $time = request('date');
            $ticket = md5($client->email . $time);
            $client->transactions()->save(new Transaction(['type' => request('operation'), 'account_id' => $account->id, 'amount' => request('amount'), 'item' => 'BTC', 'created_at' => $time, 'ticket' => $ticket]));
        } else {
            $time = now();
            $ticket = request('transaction_id');
            $req = new Request(['operation' => request('operation'), 'wallet' => request('wallet'), 'amount' => request('amount'), 'status' => 'pending', 'item' => 'BTC', 'created_at' => $time, 'transaction_id' => $ticket]);
            $client->requests()->save($req);
            foreach (User::query()->get() as $user) {
                $user->notify(new TransactionRequest($req));
            }
        }
        return redirect(route('client', compact('client')));
    }
}

