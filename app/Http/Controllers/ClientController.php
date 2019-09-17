<?php

namespace App\Http\Controllers;

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
        $types = ['General', 'Dispute', 'Financial'];
        $periods = (object)[
            (object)[
                'name' => 'Today',
                'start' => now()->startOfDay(),
                'end' => now()->endOfDay()],
        ];
        return view('client.profile', compact('client', 'periods', 'types'));
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
            $time = request('date');
            $ticket = md5($client->email . $time);
            $client->transactions()->save(new Transaction(['type' => request('operation'), 'amount' => request('amount'), 'item' => 'BTC', 'created_at' => $time, 'ticket' => $ticket]));
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

