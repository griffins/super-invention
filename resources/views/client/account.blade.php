<div class="card">
    <div class="card-status bg-teal"></div>
    <div class="card-header">
        <h3 class="card-title pt-5"><b>Account
                Balance</b> {{currency( normalize( $client->transactions()->balance()),true,8)}}
            BTC
            <br>
            <br>
        </h3>
        @if(!(user()->role =='admin' &&  user()->id ==4))
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
                            @php
                                $profit  =  $client->transactions()->whereBetween('created_at',[$period->start,$period->end])->profit();
                                $balance =  $client->transactions()->where('created_at','<=', $period->start)->balance();
                                if($profit!=0){
                                if($balance==0){
                                $profit = 100;
                                }else{
                                $profit = $profit/$balance * 100;
                                }
                                }
                            @endphp
                            <h4 class="mb-1">{{ currency( normalize($profit),true,2,false) }}%</h4>
                            <div class="text-muted" title="{{ date_range($period->start,$period->end) }}"><b>
                                    Profit
                                    ({{ $period->name }})</b>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
            <div class="col-3">
                <div class="card">
                    <div class="card-body">
                        @php
                            $profit  =  $client->transactions()->profit();
                            $balance =  $client->transactions()->deposits()->sum('amount');
                            if($profit!=0){
                            if($balance==0){
                            $profit = 100;
                            }else{
                            $profit = $profit/$balance * 100;
                            }
                            }
                        @endphp
                        <h4 class="mb-1">{{ currency( normalize($profit),true,2,false) }}%</h4>
                        <div class="text-muted" title="Total Profit">
                            <b>
                                Profit
                                (Total)
                            </b>
                        </div>
                    </div>
                </div>
            </div>
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
            <h5>Recent Transactions</h5>
            <table class="table table-striped">
                <thead>
                <tr>
                    <td><b>ID</b></td>
                    <td><b>Type</b></td>
                    <td><b>Item</b></td>
                    <td><b>Amount</b></td>
                    <td><b>Date (GMT)</b></td>
                </tr>
                </thead>
                <tbody>
                @foreach($client->transactions()->orderByDesc('created_at')->paginate(20) as $transaction)
                    <tr>
                        <td>
                            <b>
                                <div class="wrap"> {{ strtoupper( md5($transaction->ticket)) }}</div>
                            </b>
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
        @if($client->requests()->count()>0)
            <h5>Transaction Requests</h5>
            <table class="table table-striped">
                <thead>
                <tr>
                    <td><b>ID</b></td>
                    <td><b>Type</b></td>
                    <td><b>Item</b></td>
                    <td><b>Amount</b></td>
                    <td><b>Status</b></td>
                    <td><b>Date</b></td>
                </tr>
                </thead>
                <tbody>
                @foreach($client->requests()->orderByDesc('created_at')->paginate(20) as $transaction)
                    <tr>
                        <td>
                            <b>
                                <div class="wrap"> {{ strtoupper( md5($transaction->id)) }}</div>
                            </b>
                        </td>
                        <td><b>{{ ucfirst( $transaction->operation)}}</b></td>
                        <td><b>{{ $transaction->item }}</b></td>
                        <td><b>{{ currency($transaction->amount,true,8) }}</b></td>
                        <td><b>{{ ucfirst( $transaction->status) }}</b></td>
                        <td><b>{{ $transaction->created_at->diffForHumans() }}</b></td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        @endif
    </div>
</div>
