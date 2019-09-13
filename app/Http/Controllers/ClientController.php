<?php

namespace App\Http\Controllers;

use App\Client;
use App\SupportTicket;
use App\Transaction;
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
            (object)[
                'name' => 'Total',
                'start' => Carbon::parse('first day of august 2019'),
                'end' => now()->endOfYear()],
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
        $time = request('date');
        $ticket = md5($client->email . $time);
        $client->transactions()->save(new Transaction(['type' => request('operation'), 'amount' => request('amount'), 'item' => 'BTC', 'created_at' => $time, 'ticket' => $ticket]));
        return redirect(route('client',compact('client')));
    }
}

