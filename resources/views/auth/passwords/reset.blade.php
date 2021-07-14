@extends('layouts.layout')

@section('pageStyle')
<!-- page style -->
    <link rel="stylesheet" type="text/css" href="{{ asset('frontend/css/form.css') }}" />
    <link rel="stylesheet" type="text/css" href="{{ asset('frontend/css/login.css') }}" />

    <style>
        input:valid+.required { display: block; }
        .control-label-title { color: #FFF; font-size: 16px; font-weight: bold; padding: 10px 0; }
    </style>
@endSection

@section('content')
<div class="part-titlepage-main">
    <div class="container">
        <div class="box-titlepage">
            <h1 class="b-Title t-color t-align-c">{{ __('messages.reset_password') }}</h1>
        </div>
    </div>
</div>

<div class="part-content-main bb">
    <form action="{{ route('password.update') }}" method="POST">
        @csrf
        <div class="container">
            <div class="box-content-main">
                <div class="box-login">
                    <div class="box-top">
                        <label class="control-label-title">{{ __('messages.email') }}</label>
                        <input id="email" type="email" name="email" value="{{ $email ?? old('email') }}" readonly>

                        @error('email')
                            <div class="required">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="box-top">
                        <label class="control-label-title">{{ __('messages.password') }}</label>
                        <input id="password" type="password" name="password" required autocomplete="new-password">

                        @error('password')
                            <div class="required">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="box-top">
                        <label class="control-label-title">{{ __('messages.confirm_password') }}</label>
                        <input id="password-confirm" type="password" name="password_confirmation" required autocomplete="new-password">
                    </div>

                    <div class="box-bottom cf">
                        <div class="box-btn">
                            <input type="hidden" name="token" value="{{ $token }}">
                            <button type="submit" name="btnLogin" class="btn black">
                                {{ __('messages.reset_password') }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection

@section('pageScript')
@endSection
