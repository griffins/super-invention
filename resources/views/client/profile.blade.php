@extends('layouts.main')
@section('title')
    Account / {{ $client->name }}
    @if(user()->role=='admin')
        @php
            $recipients = base64_encode(json_encode([$client->email]));
            session()->put('url.intended', URL::full());
        @endphp
        <a data-turbolinks="false" href="{{route('mailbox', compact('recipients'))}}"
           class="mr-2 btn btn-primary float-right">Send Email</a>
    @endif
@endsection
@section('content')
    <div class="row">
        <div class="col-lg-4">
            <div class="card card-profile">
                <div class="card-header"
                     style="background-image: url(https://preview.tabler.io/demo/photos/eberhard-grossgasteiger-311213-500.jpg);"></div>
                <div class="card-body text-center">
                    <span class="avatar avatar-xxl mr-5 card-profile-img"
                          style="background-image: url({{$client->photo}})"></span>
                    <h3 class="mb-3">{{ $client->name }} </h3>
                    <p class="mb-4">
                        {{$client->email}}
                        <br>
                        {{ $client->phone }}
                    </p>
                </div>
            </div>
            @if(user()->role=='admin')
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Notes</h3>
                        <div class="card-options">
                            <a class="icon"
                               href="{{ route('support',['action' => 'edit','section' => 'clients','client' => $client]) }}">
                                <i class="fe fe-edit"></i>
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        <p>{{ $client->notes?: "N/A" }}</p>
                    </div>
                </div>
                <div class="card" id="ticket">
                    <div class="card-header">
                        <h3 class="card-title">Create Ticket</h3>
                    </div>
                    <div class="card-body">
                        <form method="post" action="{{ route('client.ticket',compact('client')) }}#ticket">
                            @csrf
                            <div class="form-group">
                                <label class="form-label">Ticket Type</label>
                                <select class="form-control custom-select" name="type">
                                    <option value="">Select Ticket Type</option>
                                    @foreach($types as $type)
                                        <option @if(old('type') == $type) selected @endif>{{ $type }}</option>
                                    @endforeach
                                </select>
                                @if ($errors->has('type'))
                                    <span class="invalid-feedback d-block" role="alert">
                                            <strong> {{ $errors->first('type') }}</strong>
                                    </span>
                                @endif
                            </div>
                            <div class="form-group">
                                <label class="form-label">Subject</label>
                                <input type="text" class="form-control" name="subject"
                                       placeholder="Ticket Subject.."
                                       value="{{ old('subject') }}">
                                @if ($errors->has('subject'))
                                    <span class="invalid-feedback d-block" role="alert">
                                            <strong> {{ $errors->first('subject') }}</strong>
                                    </span>
                                @endif
                            </div>

                            <div class="form-group">
                                <label class="form-label">Description</label>
                                <textarea class="form-control" name="narration" rows="6"
                                          placeholder="Narration..">{{ old('narration') }}</textarea>
                                @if ($errors->has('narration'))
                                    <span class="invalid-feedback d-block" role="alert">
                                            <strong> {{ $errors->first('narration') }}</strong>
                                    </span>
                                @endif
                            </div>
                            <div class="form-footer">
                                <button class="btn btn-primary btn-block">Submit</button>
                            </div>
                        </form>
                    </div>
                </div>
            @endif
        </div>
        <div class="col-lg-8">
            @include('client.account',['client' => $client])
        </div>
    </div>
    @if(user()->role =='admin')
        <div class="modal fade" id="transaction" tabindex="-1" role="dialog"
             aria-labelledby="exampleModalCenterTitle"
             aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <form method="POST" action="{{ route('transaction',compact('client')) }}">
                        <div class="modal-header">
                            <h5 class="modal-title" id="modalTitle">Transaction</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            </button>
                        </div>
                        <input type="hidden" name="operation" value="add">
                        <div class="modal-body">
                            @csrf
                            <div class="form-group">
                                <label class="col-form-label">Amount:</label>
                                <input type="number" name="amount" step="0.00000001" class="form-control">
                            </div>

                            <div class="form-group">
                                {{ date_picker('Date','date', now()->toDateTimeString()) }}
                            </div>

                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-primary">Submit</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @else
        <div class="modal fade" id="transaction" tabindex="-1" role="dialog"
             aria-labelledby="exampleModalCenterTitle"
             aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <form method="POST" action="{{ route('transaction',compact('client')) }}">
                        <div class="modal-header">
                            <h5 class="modal-title" id="modalTitle">Transaction</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            </button>
                        </div>
                        <input type="hidden" name="operation" value="add">
                        <div class="modal-body">
                            @csrf
                            <div class="form-group text-primary" id="transaction_deposit">
                                Please deposit the transaction amount to the bitcoin wallet below, the enter the
                                details here to facilitate the confirmation.
                                <br>
                                Deposits will become active at 0:00 GMT
                                <br>
                                <br>
                                <code>3898iVFmopLijwy2n4sRDnR5jWbSTekov4</code>

                                <img class="" src="{{ asset('images/wallet.jpg') }}">
                            </div>
                            <div class="form-group text-primary" id="transaction_withdraw">
                                Please note that withdrawals are processed within 24-48 hours.
                                <br>
                                Enter the wallet id below and the amount you wish to withdraw from your account.
                            </div>
                            <div class="form-group">
                                <label class="col-form-label">Amount:</label>
                                <input type="number" name="amount" step="0.00000001" class="form-control">
                            </div>
                            <input type="hidden" name="date" value="{{  now()->toDateTimeString() }}">

                            <div class="form-group" id="transaction_id">
                                <label class="col-form-label">Transaction ID:</label>
                                <input type="text" name="transaction_id" class="form-control">
                            </div>

                            <div class="form-group" id="wallet_id">
                                <label class="col-form-label">Your Wallet ID:</label>
                                <input type="text" value="{{ $client->wallet }}" name="wallet_id" class="form-control">
                            </div>


                            <div class="form-group text-danger">
                                Trading Currencies carries a high level of risk to your capital and you should only
                                trade with money you can afford to lose. Trading Currencies may not be suitable for all
                                investors, so please ensure that you fully understand the risks involved, and seek
                                independent advice if necessary.
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-primary">Submit</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
@endsection

@section('scripts')
    <script>
        @if(user()->role =='admin')
        function performTransaction() {
            $('#transaction').on('show.bs.modal', function (event) {
                var button = $(event.relatedTarget);
                var operation = button.data('type');
                var modal = $(this);
                if (operation === 'withdraw') {
                    modal.find('.modal-title').text('Client Withdrawal');
                } else {
                    modal.find('.modal-title').text('Client Deposit');

                }
                modal.find('.modal-content input[name=operation]').val(operation);
            })
        }

        @else
        function performTransaction() {
            $('#transaction').on('show.bs.modal', function (event) {
                var button = $(event.relatedTarget);
                var operation = button.data('type');
                var modal = $(this);

                if (operation === 'withdraw') {
                    modal.find('.modal-title').text('Withdrawal');
                    modal.find('#transaction_id').hide();
                    modal.find('#wallet_id').show();
                    modal.find('#transaction_deposit').hide();
                    modal.find('#transaction_withdraw').show();
                } else {
                    modal.find('.modal-title').text('Deposit');
                    modal.find('#transaction_id').show();
                    modal.find('#wallet_id').hide();
                    modal.find('#transaction_deposit').show();
                    modal.find('#transaction_withdraw').hide();
                }

                modal.find('.modal-content input[name=operation]').val(operation);
            })
        }

        @endif

        performTransaction()
    </script>
@endsection
