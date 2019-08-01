<div class="card">
    <div class="card-status bg-teal"></div>
    <div class="card-header">
        <h3 class="card-title">#{{$account->account}} - {{ $account->name }} ({{ $account->currency }}
            - {{currency( normalize( $account->balance),true,0)}}
            )</h3>
        <div class="card-options">
            @if(user()->club == '*')
                @if(isset($referral))
                    <a href="{{ route('referral', ['client' => $client,'account' => $account,'action' =>'delete']) }}"
                       title="Remove Referral"
                       class=""><i
                                class="fe fe-link"></i></a>
                    </a>
                @else
                    <a href="{{ route('account.show',compact('client','account')) }}" title="Edit Account"
                       class=""><i
                                class="fe fe-edit"></i></a></a>
                @endif
            @endif
            <a href="#" title="Collapse Account" class="ml-2" data-toggle="card-collapse"><i
                        class="fe fe-chevron-up"></i></a>
        </div>
    </div>
    <div class="card-alert alert alert-info mb-0">
        Commission: {{ currency($account->commission) }}% Equity: {{ currency($account->equity) }} Free
        Margin: {{ currency($account->free_margin) }}
        <i class="fe fe-info float-right" style="font-size: x-large"></i>
    </div>
    <div class="card-body">
        <div class="row">
            @foreach($periods as $period)
                <div class="col-4">
                    <div class="card">
                        <div class="card-body">
                            <h3 class="mb-1">{{ currency( normalize( $account->transactions()->whereBetween('closed_at',[$period->start,$period->end])->profit()),true,0,false) }}</h3>
                            <div class="text-muted" title="{{ date_range($period->start,$period->end) }}">Profit
                                ({{ $period->name }})
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
        @if(user()->club == '*')
            <div class="tag btn-sm tag-dark">
                Server
                <span class="tag-addon tag-success">{{$account->server->name}}</span>
            </div>
        @endif
    </div>
    <div class="card-footer">
        <a target="_blank" href="{{ route('report',['name'=>"account_ftp_statement",'account' => $account]) }}"
           class="btn btn-sm btn-primary">Account Statement (Today)</a>
        <a
                data-turbolinks="false" href="{{ route('report',['name'=>"account_statement",'account' => $account]) }}"
                class="btn btn-sm btn-primary">Account Statement (Historical)</a>
    </div>
</div>
