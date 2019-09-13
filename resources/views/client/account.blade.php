<div class="card">
    <div class="card-status bg-teal"></div>
    <div class="card-header">
        <h3 class="card-title">Withdrawal Address
            ({{currency( normalize( $client->transactions()->balance()),true,8)}} BTC)
            <br>
            <br>
            <small> {{  $client->wallet }}</small>
        </h3>
        @if(user()->role=='admin')
            <div class="card-options">
                <button data-toggle="modal" data-target="#transaction" data-type="deposit" class="btn btn-success">
                    Deposit
                </button>
                <button data-toggle="modal" data-target="#transaction" data-type="withdraw"
                        class="btn btn-primary mx-2">Withdraw
                </button>
            </div>
        @endif
    </div>
    <div class="card-body">
        <div class="row">
            @foreach($periods as $period)
                <div class="col-3">
                    <div class="card">
                        <div class="card-body">
                            <h4 class="mb-1">{{ currency( normalize( $client->transactions()->whereBetween('created_at',[$period->start,$period->end])->profit()),true,8,false) }}</h4>
                            <div class="text-muted" title="{{ date_range($period->start,$period->end) }}">Profit
                                ({{ $period->name }})
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
            <div class="col-3">
                <div class="card">
                    <div class="card-body">
                        <h4 class="mb-1">{{ currency( normalize( $client->transactions()->deposits()->sum('amount')),true,8,false) }}</h4>
                        <div class="text-muted" title="{{ date_range($period->start,$period->end) }}">
                            Deposits
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-3">
                <div class="card">
                    <div class="card-body">
                        <h4 class="mb-1">{{ currency( normalize( $client->transactions()->withdrawals()->sum('amount')),true,8,false) }} </h4>
                        <div class="text-muted" title="{{ date_range($period->start,$period->end) }}">
                            Withdrawals
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @if($client->transactions()->count()>0)
            <table class="table table-striped">
                <thead>
                <tr>
                    <td><b>ID</b></td>
                    <td><b>Type</b></td>
                    <td><b>Item</b></td>
                    <td><b>Amount</b></td>
                    <td><b>Date</b></td>
                </tr>
                </thead>
                <tbody>
                @foreach($client->transactions()->orderByDesc('created_at')->paginate(20) as $transaction)
                    <tr>
                        <td>
                            <b> <div class="wrap"> {{ strtoupper( md5($transaction->ticket)) }}</div></b>
                        </td>
                        <td><b>{{ ucfirst( $transaction->type)}}</b></td>
                        <td><b>{{ $transaction->item }}</b></td>
                        <td><b>{{ currency($transaction->amount,true,8) }}</b></td>
                        <td><b>{{ $transaction->created_at }}</b></td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        @else
            <div class="jumbotron text-center">
                No transactions
            </div>
        @endif
    </div>
</div>
