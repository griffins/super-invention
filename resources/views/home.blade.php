@extends('layouts.main')
@section('title')
    Dashboard
@endsection
@section('content')
    <div class="row mt-3 mx-0">
        <table class="table">
            <tr>
                <td colspan="1">
                    <div class="card">
                        <div class="card-body p-3 text-center">
                            <div class="text-right text-green">
                                &nbsp;
                            </div>
                            <div class="h1 m-0">{{ currency($clients->count(),true,0,true) }}</div>
                            <div class="text-muted mb-4">Clients</div>
                        </div>
                    </div>
                </td>
                <td colspan="2">
                    <div class="card">
                        <div class="card-body p-3 text-center">
                            <div class="text-right">
                                &nbsp;&nbsp;BTC
                            </div>
                            <div class="h1 m-0">{{ currency($totalFund,true,0) }}</div>
                            <div class="text-muted mb-4">Total Club Fund</div>
                        </div>
                    </div>
                </td>
            </tr>
            @foreach($periods as $x => $period)
                <td>
                    <div class="card py-5 px-4" title="{{ date_range($period->start,$period->end) }}">
                        <div class="d-flex align-items-center">
                            <div>
                                <h4 class="m-0">
                                    @php
                                        $profit  =  \App\Transaction::query()->whereBetween('created_at',[$period->start,$period->end])->profit();
                                    @endphp
                                    {{ currency($profit,true,2,!true) }}
                                    <small> BTC</small>
                                </h4>
                                <small class="text-muted"> Profit ({{ $period->name }})</small>
                            </div>
                        </div>
                    </div>
                </td>
            @endforeach
        </table>
    </div>
@endsection