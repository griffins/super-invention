<?php

namespace App\Http\Controllers;

use App\Client;
use App\Transaction;
use Carbon\Carbon;

class HomeController extends Controller
{

    /**
     * HomeController constructor.
     */
    public function __construct()
    {
        $this->middleware('auth:admin,client');
    }

    public function index()
    {
        if (user()->role == 'admin') {
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
                    'end' => $month->copy()->addMonth()->firstOfMonth(Carbon::SATURDAY)->subDay()->endOfDay()],
                (object)[
                    'name' => 'Last Month',
                    'start' => now()->subMonth()->startOfMonth()->firstOfMonth(Carbon::SATURDAY)->startOfDay(),
                    'end' => $month->copy()->addMonth()->firstOfMonth(Carbon::SATURDAY)->subDay()->endOfDay()],
                (object)[
                    'name' => 'This Year',
                    'start' => now()->startOfYear(),
                    'end' => now()->endOfYear()],
            ];
            $clients = Client::query();
            $totalFund =0;
            return view('home', compact('clients', 'totalFund', 'periods'));
        } else {
            return redirect(route('client', ['client' => user()]));
        }
    }

    public function clientDashboard(Client $client)
    {
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
                'end' => $month->copy()->addMonth()->firstOfMonth(Carbon::SATURDAY)->subDay()->endOfDay()],
            (object)[
                'name' => 'Last Month',
                'start' => now()->subMonth()->startOfMonth()->firstOfMonth(Carbon::SATURDAY)->endOfDay(),
                'end' => now()->firstOfMonth(Carbon::SATURDAY)->subDay()->endOfDay()],
            (object)[
                'name' => 'This Year',
                'start' => now()->startOfYear(),
                'end' => now()->endOfYear()],
        ];

        $accounts = $client->accounts();
        $referrals = $client->referrals();
        $currencies = [];
        $r_currencies = [];
        foreach ($client->accounts()->distinct()->get('currency') as $cur) {
            $currency = [
                'code' => $cur->currency,
                'accounts' => $client->accounts()->where('currency', $cur->currency),
                'transactions' => Transaction::query()
                    ->whereIn('account_id', $client->accounts()->where('currency', $cur->currency)->pluck('id'))
            ];
            $currencies[$cur->currency] = $currency;
        }
        foreach ((clone $referrals)->distinct()->get(['currency']) as $cur) {
            $currency = [
                'code' => $cur->currency,
                'accounts' => (clone $referrals)->where('currency', $cur->currency),
                'transactions' => Transaction::query()
                    ->whereIn('account_id', (clone $referrals)->where('currency', $cur->currency)->pluck('accounts.id'))
            ];
            $r_currencies[$cur->currency] = $currency;
        }
        return view('client.dashboard', compact('accounts', 'referrals', 'periods', 'client', 'currencies', 'r_currencies'));
    }
}
