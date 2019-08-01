@extends('layouts.main')
@section('title')
    Add Referral
@endsection
@section('content')
    <div class="card">
        <div class="card-header">
            Account Details
        </div>
        <div class="card-body">
            <small>
                Please fill the details below.
            </small>
            <form action="{{ route('referral',compact('client')) }}" method="post">
                @csrf
                <div class="row mt-3">
                    <div class="col-4 form-group">
                        <label>Account Number</label>
                        <div class="input-icon mb-3">
                            <input type="text" name="account" value="{{ old('account') }}" class="form-control"
                                   placeholder="Search for...">
                            <span class="input-icon-addon">
                            <div class="spinner-border d-none spinner-border-sm text-primary" role="status">
  <span class="sr-only">Loading...</span>
</div>
                            </span>
                        </div>

                        @if ($errors->has('account_id'))
                            <span class="invalid-feedback d-block" role="alert">
                                        <strong>{{ $errors->first('account_id') }}</strong>
                                    </span>
                        @endif
                    </div>
                    <div class="col-6">
                        <label>Account Name</label>
                        <p id="accountName" class="form-control">Not Available</p>
                        @if ($errors->has('name'))
                            <span class="invalid-feedback d-block" role="alert">
                                        <strong>{{ $errors->first('name') }}</strong>
                                    </span>
                        @endif
                    </div>
                    <input name="account_id" type="hidden">
                </div>
                <div class="row mt-3">
                    <div class="col-6">
                        <button class="btn btn-primary">Submit Application</button>
                        <a href="{{ route('client',compact('client')) }}" class="btn btn-outline-primary">
                            Cancel
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection
@section('scripts')
    <script>
        $(document).ready(function () {
            $('input[name=account]').on('change', function () {
                search($(this).val());
            }).on('keyup', function () {
                search($(this).val());
            });
        });

        function search(val) {
            $('.spinner-border').toggleClass("d-none");
            axios.get('?query=' + val)
                .then(function (response) {
                    $("#accountName").html("{0} ({1})".format(response.data.name, response.data.account));
                    $('input[name=account_id]').val(response.data.id);
                })
                .catch(function (error) {
                    $("#accountName").html("N/A");
                    $('input[name=account_id]').val("");
                }).then(function () {
                $('.spinner-border').toggleClass("d-none");
            })
        }
    </script>
@endsection