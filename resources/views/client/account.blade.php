<div class="card">
    <div class="card-status bg-teal"></div>
    <div class="card-header">
        <h3 class="card-title">{{ $client->name }} (BTC - {{currency( normalize( $client->transactions()->balance()),true,0)}})</h3>
        <div class="card-options">
            <a href="#" title="Collapse Account" class="ml-2" data-toggle="card-collapse"><i
                        class="fe fe-chevron-up"></i></a>
        </div>
    </div>
    <div class="card-body">
        <div class="row">
            @foreach($periods as $period)
                <div class="col-4">
                    <div class="card">
                        <div class="card-body">
                            <h3 class="mb-1">{{ currency( normalize( $client->transactions()->whereBetween('created_at',[$period->start,$period->end])->profit()),true,0,false) }}</h3>
                            <div class="text-muted" title="{{ date_range($period->start,$period->end) }}">Profit
                                ({{ $period->name }})
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
    <div class="card-footer">
        <a
                data-turbolinks="false" href="{{ route('report',['name'=>"account_statement",'account' => $client]) }}"
                class="btn btn-sm btn-primary">Account Statement (Historical)</a>
    </div>
</div>
