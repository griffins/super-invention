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
                            <div class="h1 m-0">{{ currency($totalFund,true,6) }}</div>
                            <div class="text-muted mb-4">Total Club Fund</div>
                        </div>
                    </div>
                </td>
                @foreach($periods as $x => $period)
                    <td colspan="2">
                        <div class="card">
                            <div class="card-body p-3 text-center">
                                <div class="text-right">
                                    &nbsp;&nbsp;BTC
                                </div>
                                @php
                                    $profit  =  \App\Transaction::query()->whereBetween('created_at',[$period->start,$period->end])->profit();
                                @endphp
                                <div class="h1 m-0">{{ currency($profit,true,6,!true) }}</div>
                                <div class="text-muted mb-4"> Profit ({{ $period->name }})</div>
                            </div>
                        </div>
                    </td>
            @endforeach
        </table>
        <div class="card mx-3">
            <table class="table table-striped">
                <thead>
                <tr>
                    <td>ID</td>
                    <td>Item</td>
                    <td>Amount</td>
                    <td>Date</td>
                </tr>
                </thead>
                <tbody>
                @foreach(\App\AcruedAmount::query()->paginate(20) as $interest)
                    <tr>
                        <td>{{ md5( $interest->message_id) }}</td>
                        <td>{{ $interest->item }}</td>
                        <td>{{ currency( $interest->amount,true,8) }}</td>
                        <td>{{ $interest->created_at }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection