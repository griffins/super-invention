@extends('layouts.main')
@section('title')
    {{$user->name}}
@endsection
@section('content')
    <div class="row">
        <div class="col-lg-4">
            <div class="card card-profile">
                <div class="card-header"
                     style="background-image: url(https://tabler.io/tabler/demo/photos/eberhard-grossgasteiger-311213-500.jpg)"></div>
                <div class="card-body text-center">
                    <span class="card-profile-img avatar avatar-xxl"
                          style="background-image: url({{ auth()->user()->photo }})">
  <a href="javascript:void(0)" onclick="changeProfile()" class="avatar-status fe fe-camera"
     style="background: transparent; font-size: 16px;text-decoration: none">
  </a>

</span>

                    <h3 class="mb-3">    {{$user->name}}</h3>
                    <form method="post" id="profile-form" action="{{ route('profile.update') }}"
                          enctype="multipart/form-data">
                        @csrf
                        <input name="image" id="profile" type="file" style="display: none"
                               accept="image/png, image/jpeg">
                    </form>
                </div>
            </div>
        </div>
        <div class="col-lg-8">
            <div class="row">
                <div class="col-md-12">
                    <div class="card card-shadow" style="border-radius: 4px;overflow: hidden">
                        <div class="card-header">
                            Personal Details
                        </div>
                        <div class="card-body">
                            <form method="post" action="{{ route('profile.update') }}">
                                <div class="row">
                                    <div class="col-6">
                                        <label>Name</label>
                                        <input type="text" name="name" value="{{ old('name',$user->name) }}"
                                               class="form-control"
                                               placeholder="Name">
                                        @if ($errors->has('name'))
                                            <span class="invalid-feedback d-block" role="alert">
                                        <strong>{{ $errors->first('name') }}</strong>
                                    </span>
                                        @endif
                                    </div>
                                    <div class="col-6">
                                        <label>Email</label>
                                        <input type="text" name="email" value="{{ old('email',$user->email) }}"
                                               class="form-control d-inline"
                                               placeholder="Email">
                                        @if ($errors->has('email'))
                                            <span class="invalid-feedback d-block" role="alert">
                                                 <strong>{{ $errors->first('email') }}</strong>
                                            </span>
                                        @endif
                                    </div>
                                </div>
                                @csrf
                                @if($user->role !=='admin')
                                    <div class="row mt-3">
                                        <div class="col-12">
                                            <label>Wallet</label>
                                            <input type="text" name="wallet"
                                                   value="{{ old('wallet',$user->wallet) }}"
                                                   class="form-control d-inline"
                                                   placeholder="Wallet Address">
                                            @if ($errors->has('wallet'))
                                                <span class="invalid-feedback d-block" role="alert">
                                        <strong>{{ $errors->first('wallet') }}</strong>
                                    </span>
                                            @endif
                                        </div>
                                    </div>
                                @endif
                                <div class="row mt-3">
                                    <div class="col-6">
                                        <button class="btn btn-primary">Update</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                <div class="col-md-12" id="password">
                    <div class="card card-shadow" style="border-radius: 4px;overflow: hidden">
                        <div class="card-header">
                            Change Password
                        </div>
                        <div class="card-body">
                            <form method="post" action="{{ route('password.change') }}">
                                @csrf
                                <div class="row">
                                    <div class="col-12">
                                        <label>Current Password</label>
                                        <input type="password" name="current_password" value=""
                                               class="form-control"
                                               placeholder="Current Password">
                                        @if ($errors->has('current_password'))
                                            <span class="invalid-feedback d-block" role="alert">
                                        <strong>{{ $errors->first('current_password') }}</strong>
                                    </span>
                                        @endif
                                    </div>
                                </div>
                                <div class="row mt-3">
                                    <div class="col-6">
                                        <label>New Password</label>
                                        <input type="password" name="password" value=""
                                               class="form-control d-inline"
                                               placeholder="New Password">
                                        @if ($errors->has('password'))
                                            <span class="invalid-feedback d-block" role="alert">
                                        <strong>{{ $errors->first('password') }}</strong>
                                    </span>
                                        @endif
                                    </div>
                                    <div class="col-6">
                                        <label>Password Confirmation</label>
                                        <input type="password" name="password_confirmation" value=""
                                               class="form-control d-inline"
                                               placeholder="Password Confirmation">
                                        @if ($errors->has('password_confirmation'))
                                            <span class="invalid-feedback d-block" role="alert">
                                        <strong>{{ $errors->first('password_confirmation') }}</strong>
                                    </span>
                                        @endif
                                    </div>
                                </div>
                                <div class="row mt-3">
                                    <div class="col-6">
                                        <button class="btn btn-primary">Change</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('scripts')
    <script>
        function changeProfile() {
            $("#profile").trigger('click')
        }

        $(document).ready(function () {
            $("#profile").on('change', function () {
                document.getElementById('profile-form').submit();
            });
        });
    </script>
@endsection