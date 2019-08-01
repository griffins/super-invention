<?php

namespace App\Http\Controllers\Reports;


use App\CryptoTrade;
use Illuminate\Http\Request;

trait CryptoReports
{
    private function cryptoDashboard(Request $request, $report)
    {
        $query = CryptoTrade::query();
        return view('reports.crypto', compact('query', 'report'));
    }

    private function cryptoTrades(Request $request, $report)
    {
        $trades = CryptoTrade::query()->orderByDesc('date')->paginate();
        return view('reports.crypto_list', compact('trades', 'report'));
    }
}