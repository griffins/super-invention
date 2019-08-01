<?php

namespace App\Http\Controllers;

use App\Client;
use App\Invoice;
use App\SupportTicket;
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
        $month = now()->firstOfMonth(Carbon::SATURDAY)->startOfDay();
        if ($month->greaterThan(now())) {
            $month = now()->subMonth()->firstOfMonth(Carbon::SATURDAY)->startOfDay();
        }
        $periods = (object)[
            (object)[
                'name' => 'Today',
                'start' => now()->startOfDay(),
                'end' => now()->endOfDay()],
            (object)[
                'name' => 'Yesterday',
                'start' => now()->subDay()->startOfDay(),
                'end' => now()->subDay()->endOfDay()],
            (object)[
                'name' => 'Current Week',
                'start' => now()->startOfWeek(),
                'end' => now()->endOfWeek()],
            (object)[
                'name' => 'Last Week',
                'start' => now()->subWeek()->startOfWeek(),
                'end' => now()->subWeek()->endOfWeek()],
            (object)[
                'name' => 'This Month',
                'start' => $month,
                'end' => $month->copy()->addMonth()->endOfDay()],
//            (object)[
//                'name' => 'Last Month',
//                'start' => now()->subMonth()->startOfMonth(),
//                'end' => now()->subMonth()->endOfMonth()],
            (object)[
                'name' => 'This Year',
                'start' => now()->startOfYear(),
                'end' => now()->endOfYear()],
        ];
        return view('client.profile', compact('client' , 'periods', 'types'));
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
    }

