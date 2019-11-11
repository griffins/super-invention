@extends('layouts.single')

@section('content')
    <div class="container">
        <div class="row">
            <div class="col col-10 mx-auto">
                <form method="POST" class="card" enctype="multipart/form-data" action="{{ route('register') }}">
                    @csrf
                    <div class="card-body p-6">
                        <div class="card-title">Create your account</div>
                        <div class="row">
                            <div class="col-7">
                                <div class="form-group">
                                    <label for="name" class="col-form-label">{{ __('Name') }}</label>

                                    <input id="name" type="text"
                                           class="form-control{{ $errors->has('name') ? ' is-invalid' : '' }}"
                                           name="name"
                                           value="{{ old('name') }}" required autofocus>
                                    @if ($errors->has('name'))
                                        <span class="invalid-feedback" role="alert">
                                        <strong>{{ $errors->first('name') }}</strong>
                                    </span>
                                    @endif
                                </div>
                            </div>
                            <div class="col-5">
                                <div class="form-group">
                                    <label for="email"
                                           class="col-form-label">{{ __('E-Mail Address') }}</label>
                                    <input id="email" type="email"
                                           class="form-control{{ $errors->has('email') ? ' is-invalid' : '' }}"
                                           name="email" value="{{ old('email') }}" required>

                                    @if ($errors->has('email'))
                                        <span class="invalid-feedback" role="alert">
                                        <strong>{{ $errors->first('email') }}</strong>
                                    </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-4">
                                <div class="form-group">
                                    <label for="email"
                                           class="col-form-label">{{ __('Phone Number') }}</label>
                                    <input id="phone" type="phone"
                                           class="form-control{{ $errors->has('phone') ? ' is-invalid' : '' }}"
                                           name="phone" value="{{ old('phone') }}" required>
                                    @if ($errors->has('phone'))
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $errors->first('phone') }}</strong>
                                        </span>
                                    @endif
                                </div>
                            </div>
                            <div class="col-8">
                                <div class="form-group">
                                    <label for="email"
                                           class="col-form-label">{{ __('Address') }}</label>
                                    <input id="phone" type="text"
                                           class="form-control{{ $errors->has('address') ? ' is-invalid' : '' }}"
                                           name="address" value="{{ old('address') }}" required>

                                    @if ($errors->has('address'))
                                        <span class="invalid-feedback" role="alert">
                                        <strong>{{ $errors->first('address') }}</strong>
                                    </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-6">
                                <div class="form-group">
                                    <label for="email"
                                           class="col-form-label">{{ __('Referee') }}</label>
                                    <input id="phone" type="text"
                                           class="form-control{{ $errors->has('referee') ? ' is-invalid' : '' }}"
                                           name="referee" value="{{ old('referee') }}" required>
                                    @if ($errors->has('referee'))
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $errors->first('referee') }}</strong>
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-4">
                                <div class="form-group">
                                    <label for="email"
                                           class="col-form-label">{{ __('ID Type') }}</label>
                                    <select name="id_type" class="form-control">
                                        <option>National ID</option>
                                        <option @if(old('type_id')=='Passport ID') selected @endif>Passport ID
                                        </option>
                                    </select>

                                    @if ($errors->has('id_type'))
                                        <span class="invalid-feedback" role="alert">
                                        <strong>{{ $errors->first('id_type') }}</strong>
                                    </span>
                                    @endif
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="form-group">
                                    <label for="password"
                                           class="col-form-label">{{ __('Number') }}</label>
                                    <input id="number" type="text"
                                           value="{{ old('number') }}"
                                           class="form-control{{ $errors->has('number') ? ' is-invalid' : '' }}"
                                           name="number" required>

                                    @if ($errors->has('number'))
                                        <span class="invalid-feedback" role="alert">
                                        <strong>{{ $errors->first('number') }}</strong>
                                    </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-4">
                                <div class="form-group">
                                    <label for="password-confirm"
                                           class="col-form-label text-md-right">{{ __('Password') }}</label>
                                    <input id="password" type="password" class="form-control"
                                           name="password" required>
                                    @if ($errors->has('password'))
                                        <span class="invalid-feedback" role="alert">
                                        <strong>{{ $errors->first('password') }}</strong>
                                    </span>
                                    @endif
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="form-group">
                                    <label for="password-confirm"
                                           class="col-form-label text-md-right">{{ __('Confirm Password') }}</label>

                                    <input id="password-confirm" type="password" class="form-control"
                                           name="password_confirmation" required>
                                    @if ($errors->has('password_confirmation'))
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $errors->first('password_confirmation') }}</strong>
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <table class="table">
                            <tr>
                                <td>
                                    <label for="password-confirm"
                                           class="col-form-label text-md-right">{{ __('Selfie Photo holding ID') }}</label>
                                </td>
                                <td>
                                    <input id="selfie" type="file"
                                           name="selfie">
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <label for="password-confirm"
                                           class="col-form-label text-md-right">{{ __('Photo of ID') }}</label></td>
                                <td>
                                    <input id="photo_id" type="file"
                                           name="photo_id">
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <label for="password-confirm"
                                           class="col-form-label text-md-right">{{ __('Proof Address (Less than 90 days old)') }}</label>
                                </td>
                                <td>
                                    <input id="proof_address" type="file"
                                           name="proof_address">
                                </td>
                            </tr>
                        </table>
                        <div class="form-group mb-0">
                            <button type="submit" class="btn btn-primary">
                                {{ __('Register') }}
                            </button>
                        </div>
                    </div>
                </form>
                <div class="text-center">
                    <a href="{{ route('login') }}" style="color:blue"> Already have account? Sign in</a>
                </div>
            </div>
        </div>
    </div>
@endsection
